<?php

namespace App\Http\Controllers\API;

use App\Enum\TravelType;
use App\Exceptions\Checkin\AlreadyCheckedInException;
use App\Exceptions\CheckInCollisionException;
use App\Exceptions\HafasException;
use App\Exceptions\StationNotOnTripException;
use App\Http\Controllers\Backend\Transport\TrainCheckinController;
use App\Http\Controllers\Backend\User\DashboardController;
use App\Http\Controllers\Controller;
use App\Http\Controllers\HafasController;
use App\Http\Controllers\StatusController as StatusBackend;
use App\Http\Controllers\TransportController as TransportBackend;
use App\Http\Controllers\UserController as UserBackend;
use App\Http\Resources\HafasTripResource;
use App\Http\Resources\StopoverResource;
use App\Http\Resources\TrainStationResource;
use App\Models\HafasTrip;
use App\Models\TrainStation;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Enum;
use Throwable;

/**
 * @deprecated
 */
class LegacyApi0Controller extends Controller
{

    public function sendResponse($response): JsonResponse {
        $disclaimer = 'APIv0 is deprecated and will be removed within the next days __WITHOUT ANY OFFICIAL NOTICE__. Please use APIv1 instead.';
        if (is_array($response)) {
            $response = array_merge(['disclaimer' => $disclaimer], $response);
        }
        return response()->json($response);
    }

    public function sendError(array|string $error, int $code = 404): JsonResponse {
        $response = [
            'error' => $error,
        ];
        return response()->json($response, $code);
    }

    public function getUser(Request $request): JsonResponse {
        $user = $request->user();
        if ($user) {
            return $this->sendResponse($user);
        }
        return $this->sendResponse('user not found');

    }

    public function showUser($username): JsonResponse {
        return $this->sendResponse(UserBackend::getProfilePage($username));
    }

    public function getActiveStatuses($username) {
        //Somehow this breaks without a LIKE.
        $user           = User::where('username', 'LIKE', $username)->firstOrFail();
        $statusResponse = StatusBackend::getActiveStatuses($user->id, true);
        return $this->sendResponse($statusResponse);
    }

    public function showStatuses(Request $request): JsonResponse {
        $validator = Validator::make($request->all(), [
            'maxStatuses' => 'integer',
            'username'    => 'string|required_if:view,user',
            'view'        => 'in:user,global,personal'
        ]);

        if ($validator->fails()) {
            return $this->sendError($validator->errors(), 400);
        }

        $view = 'global';
        if (!empty($request->view)) {
            $view = $request->view;
        }
        $statuses = ['statuses' => ''];
        if ($view === 'global') {
            $statuses['statuses'] = DashboardController::getGlobalDashboard(Auth::user());
        }
        if ($view === 'personal') {
            $statuses['statuses'] = DashboardController::getPrivateDashboard(Auth::user());
        }
        if ($view === 'user') {
            $statuses = UserBackend::getProfilePage($request->username);
        }
        return response()->json($statuses['statuses']);
    }

    public function showStationboard(Request $request): JsonResponse {
        $validator = Validator::make($request->all(), [
            'station'    => ['required', 'string'],
            'when'       => ['nullable', 'date'],
            'travelType' => ['nullable', new Enum(TravelType::class)],
        ]);

        if ($validator->fails()) {
            return $this->sendError($validator->errors(), 400);
        }

        $validated = $validator->validate();

        try {
            $trainStationboardResponse = TransportBackend::getDepartures(
                stationQuery: $validated['station'],
                when:         isset($validated['when']) ? Carbon::parse($validated['when']) : null,
                travelType:   TravelType::tryFrom($validated['travelType'] ?? null),
            );
        } catch (HafasException $exception) {
            return $this->sendError($exception->getMessage(), 503);
        } catch (ModelNotFoundException) {
            return $this->sendError(__('controller.transport.no-station-found'), 404);
        }

        return $this->sendResponse([
                                       'station'    => $trainStationboardResponse['station'],
                                       'when'       => $trainStationboardResponse['times']['now'],
                                       'departures' => $trainStationboardResponse['departures']
                                   ]);
    }

    public function showTrip(Request $request) {
        $validator = Validator::make($request->all(), [
            'tripID'   => 'required',
            'lineName' => 'required',
            'start'    => 'required'
        ]);

        if ($validator->fails()) {
            return $this->sendError($validator->errors(), 400);
        }

        try {
            $hafasTrip = TrainCheckinController::getHafasTrip(
                tripId:   $request->tripID,
                lineName: $request->lineName,
                startId:  $request->start,
            );
            return $this->sendResponse([
                                           'start'       => new TrainStationResource($hafasTrip->originStation),
                                           'destination' => new TrainStationResource($hafasTrip->destinationStation),
                                           'train'       => new HafasTripResource($hafasTrip),
                                           'stopovers'   => StopoverResource::collection($hafasTrip->stopoversNEW),
                                       ]);
        } catch (StationNotOnTripException) {
            return $this->sendError(__('controller.transport.not-in-stopovers'), 400);
        }
    }

    public function checkin(Request $request) {
        $validator = Validator::make($request->all(), [
            'tripID'      => ['required'],
            'lineName'    => ['nullable'], //Should be required in future API Releases due to DB Rest
            'start'       => ['required', 'numeric'],
            'destination' => ['required', 'numeric'],
            'body'        => ['nullable', 'max:280'],
            'tweet'       => ['nullable', 'boolean'],
            'toot'        => ['nullable', 'boolean'],
            //nullable, so that it is not a breaking change
            'departure'   => ['nullable', 'date'],
            'arrival'     => ['nullable', 'date'],
        ]);
        if ($validator->fails()) {
            return $this->sendError($validator->errors(), 400);
        }
        $hafasTrip = HafasTrip::where('trip_id', $request->input('tripID'))->first();

        if ($hafasTrip === null && strlen($request->input('lineName')) == 0) {
            return $this->sendError('Please specify the trip with lineName.', 400);
        }

        if ($hafasTrip === null) {
            try {
                $hafasTrip = HafasController::getHafasTrip($request->input('tripID'), $request->input('lineName'));
            } catch (HafasException $exception) {
                return $this->sendError($exception->getMessage(), 400);
            }
        }

        try {
            $origin = TrainStation::where('ibnr', $request->input('start'))->first();
            if (isset($request->departure)) {
                $departure = Carbon::parse($request->input('departure'));
            } else {
                //Legacy: Get best matching timestamp from stopovers... it's just APIv0
                $departure = $hafasTrip->stopoversNEW->where('train_station_id', $origin->id)->first()->departure_planned;
            }
            $destination = TrainStation::where('ibnr', $request->input('destination'))->first();
            if (isset($request->arrival)) {
                $arrival = Carbon::parse($request->input('arrival'));
            } else {
                //Legacy: Get best matching timestamp from stopovers... it's just APIv0
                $arrival = $hafasTrip->stopoversNEW->where('train_station_id', $destination->id)->first()->arrival_planned;
            }

            $backendResponse = TrainCheckinController::checkin(
                user:           Auth::user(),
                hafasTrip:      $hafasTrip,
                origin:         $origin,
                departure:      $departure,
                destination:    $destination,
                arrival:        $arrival,
                body:           $request->input('body'),
                postOnTwitter:  isset($request->tweet) && $request->tweet,
                postOnMastodon: isset($request->toot) && $request->toot,
            );

            $trainCheckin = $backendResponse['status']->trainCheckin;

            return $this->sendResponse([
                                           'distance'             => $trainCheckin['distance'] / 1000,
                                           'duration'             => $trainCheckin['duration'],
                                           'statusId'             => $backendResponse['status']->id,
                                           'points'               => $trainCheckin['points'],
                                           'lineName'             => $trainCheckin['lineName'],
                                           'alsoOnThisConnection' => $trainCheckin['alsoOnThisConnection']
                                               ->map(function($status) {
                                                   return $status->user;
                                               })
                                       ]);

        } catch (CheckInCollisionException $exception) {
            return $this->sendError([
                                        'status_id' => $exception->getCollision()->status_id,
                                        'lineName'  => $exception->getCollision()->HafasTrip->first()->linename
                                    ], 409);
        } catch (StationNotOnTripException $exception) {
            report($exception);
            return $this->sendError('Given stations are not on the trip.', 400);
        } catch (AlreadyCheckedInException) {
            return $this->sendError(__('messages.exception.already-checkedin', [], 'en'), 400);
        } catch (Throwable $exception) {
            report($exception);
            return $this->sendError('Unknown Error occurred', 500);
        }

    }

    public function showStationByCoordinates(Request $request) {
        $validator = Validator::make($request->all(), [
            'latitude'  => 'required|numeric|min:-180|max:180',
            'longitude' => 'required|numeric|min:-180|max:180'
        ]);
        if ($validator->fails()) {
            return $this->sendError($validator->errors(), 400);
        }

        $nearestStation = HafasController::getNearbyStations($request->latitude, $request->longitude, 1)->first();
        if ($nearestStation === null) {
            return $this->sendError(__("controller.transport.no-station-found"), 404);
        }

        return $this->sendResponse($nearestStation);
    }
}
