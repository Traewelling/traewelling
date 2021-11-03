<?php

namespace App\Http\Controllers;

use App\Enum\StatusVisibility;
use App\Enum\TravelType;
use App\Exceptions\CheckInCollisionException;
use App\Exceptions\HafasException;
use App\Exceptions\TrainCheckinAlreadyExistException;
use App\Http\Controllers\Backend\EventController as EventBackend;
use App\Http\Controllers\TransportController as TransportBackend;
use Carbon\Carbon;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Throwable;

/**
 * @deprecated Content will be moved to the backend/frontend/API packages soon, please don't add new functions here!
 */
class FrontendTransportController extends Controller
{
    public function TrainAutocomplete(string $station): JsonResponse {
        try {
            $TrainAutocompleteResponse = TransportBackend::getTrainStationAutocomplete($station);
            return response()->json($TrainAutocompleteResponse);
        } catch (HafasException $e) {
            abort(503, $e->getMessage());
        }
    }

    public function TrainStationboard(Request $request): Renderable|RedirectResponse {

        $validated = $request->validate([
                                            'station'    => ['required', 'string'],
                                            'when'       => ['nullable', 'date'],
                                            'travelType' => ['nullable', Rule::in(TravelType::getList())]
                                        ]);

        $when = isset($validated['when']) ? Carbon::parse($validated['when']) : null;

        try {
            $TrainStationboardResponse = TransportBackend::getDepartures(
                $validated['station'],
                $when,
                $validated['travelType'] ?? null
            );
        } catch (HafasException $exception) {
            return back()->with('error', $exception->getMessage());
        } catch (ModelNotFoundException) {
            return redirect()->back()->with('error', __('controller.transport.no-station-found'));
        }

        return view('stationboard', [
                                      'station'    => $TrainStationboardResponse['station'],
                                      'departures' => $TrainStationboardResponse['departures'],
                                      'times'      => $TrainStationboardResponse['times'],
                                      'request'    => $request,
                                      'latest'     => TransportController::getLatestArrivals(Auth::user())
                                  ]
        );
    }

    public function StationByCoordinates(Request $request): RedirectResponse {
        $validatedInput = $request->validate([
                                                 'latitude'  => ['required', 'numeric', 'min:-90', 'max:90'],
                                                 'longitude' => ['required', 'numeric', 'min:-180', 'max:180'],
                                             ]);
        try {
            $nearestStation = HafasController::getNearbyStations(
                $validatedInput['latitude'], $validatedInput['longitude'], 1
            )->first();
        } catch (HafasException) {
            return back()->with('error', __('messages.exception.generalHafas'));
        }

        if ($nearestStation === null) {
            return back()->with('error', __('controller.transport.no-station-found'));
        }

        return redirect()->route('trains.stationboard', [
            'station'  => $nearestStation->name,
            'provider' => 'train'
        ]);
    }

    public function TrainTrip(Request $request): Renderable|RedirectResponse {

        $request->validate([
                               'tripID'    => ['required'],
                               'lineName'  => ['required'],
                               'start'     => ['required', 'numeric'],
                               'departure' => ['required', 'date']
                           ]);

        try {
            $TrainTripResponse = TransportBackend::TrainTrip(
                $request->tripID,
                $request->lineName,
                $request->start,
                Carbon::parse($request->departure)
            );
        } catch (HafasException $exception) {
            return back()->with('error', $exception->getMessage());
        }
        if ($TrainTripResponse === null) {
            return redirect()->back()->with('error', __('controller.transport.not-in-stopovers'));
        }

        // Find out where this train terminates and offer this as a "fast check-in" option.
        $terminalStopIndex = count($TrainTripResponse['stopovers']) - 1;
        while ($terminalStopIndex >= 1 && @$TrainTripResponse['stopovers'][$terminalStopIndex]['cancelled'] == true) {
            $terminalStopIndex--;
        }
        $terminalStop = $TrainTripResponse['stopovers'][$terminalStopIndex];

        return view('trip', [
            'hafasTrip'    => $TrainTripResponse['hafasTrip'],
            'destination'  => $TrainTripResponse['destination'], //deprecated. use hafasTrip->destinationStation instead
            'events'       => EventBackend::activeEvents(),
            'start'        => $TrainTripResponse['start'], //deprecated. use hafasTrip->originStation instead
            'stopovers'    => $TrainTripResponse['stopovers'],
            'terminalStop' => $terminalStop,
            'user'         => Auth::user(),
        ]);
    }

    public function TrainCheckin(Request $request): RedirectResponse {
        $this->validate($request, [
            'body'              => 'max:280',
            'business_check'    => 'digits_between:0,2',
            'checkinVisibility' => Rule::in(StatusVisibility::getList()),
            'tweet_check'       => 'max:2',
            'toot_check'        => 'max:2',
            'event'             => 'integer',
            'departure'         => ['required', 'date'],
            'arrival'           => ['required', 'date'],
        ]);
        try {
            $trainCheckin = TransportBackend::TrainCheckin(
                $request->tripID,
                $request->start,
                $request->destination,
                $request->body,
                Auth::user(),
                $request->business_check,
                $request->tweet_check,
                $request->toot_check,
                $request->checkinVisibility,
                $request->event,
                Carbon::parse($request->departure),
                Carbon::parse($request->arrival),
            );

            return redirect()->route('dashboard')->with('checkin-success', [
                'distance'             => $trainCheckin['distance'],
                'duration'             => $trainCheckin['duration'],
                'points'               => $trainCheckin['points'],
                'lineName'             => $trainCheckin['lineName'],
                'alsoOnThisConnection' => $trainCheckin['alsoOnThisConnection'],
                'event'                => $trainCheckin['event']
            ]);

        } catch (CheckInCollisionException $exception) {

            return redirect()
                ->route('dashboard')
                ->with('error', __(
                    'controller.transport.overlapping-checkin',
                    [
                        'url'      => url('/status/' . $exception->getCollision()->status->id),
                        'id'       => $exception->getCollision()->status->id,
                        'linename' => $exception->getCollision()->HafasTrip->linename
                    ]
                ));

        } catch (TrainCheckinAlreadyExistException) {
            return redirect()->route('dashboard')->with('error', __('messages.exception.general'));
        } catch (Throwable $exception) {
            report($exception);
            return redirect()
                ->route('dashboard')
                ->with('error', __('messages.exception.general'));

        }

    }

    public function setTrainHome(Request $request): RedirectResponse {
        $validated = $request->validate([
                                            'stationName' => ['required', 'max:255']
                                        ]);

        try {
            $trainStation = TransportBackend::setTrainHome(auth()->user(), $validated['stationName']);

            return redirect()->back()->with(['message' => __('user.home-set', ['station' => $trainStation->name])]);
        } catch (HafasException) {
            return redirect()->back()->with(['error' => __('messages.exception.generalHafas')]);
        }
    }

    public function FastTripAccess(Request $request): RedirectResponse {
        $fastTripResponse = TransportBackend::FastTripAccess($request->start,
                                                             $request->lineName,
                                                             $request->number,
                                                             $request->when);
        if ($fastTripResponse === null) {
            abort(404);
        }
        return redirect()->route('trains.trip', [
            'tripID'   => $fastTripResponse->tripId,
            'lineName' => $fastTripResponse->line->name,
            'start'    => $request->start
        ]);
    }
}
