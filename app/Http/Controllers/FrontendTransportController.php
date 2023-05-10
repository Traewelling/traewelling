<?php

namespace App\Http\Controllers;

use App\Enum\Business;
use App\Enum\PointReason;
use App\Enum\StatusVisibility;
use App\Enum\TravelType;
use App\Exceptions\Checkin\AlreadyCheckedInException;
use App\Exceptions\CheckInCollisionException;
use App\Exceptions\HafasException;
use App\Exceptions\StationNotOnTripException;
use App\Exceptions\TrainCheckinAlreadyExistException;
use App\Http\Controllers\Backend\Transport\HomeController;
use App\Http\Controllers\Backend\Transport\TrainCheckinController;
use App\Http\Controllers\TransportController as TransportBackend;
use App\Models\Event;
use App\Models\HafasTrip;
use App\Models\TrainStation;
use App\Models\TrainStopover;
use Carbon\Carbon;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Enum;
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
                                            'station'    => ['required_without:ibnr'],
                                            'ibnr'       => ['required_without:station', 'numeric'],
                                            'when'       => ['nullable', 'date'],
                                            'travelType' => ['nullable', new Enum(TravelType::class)]
                                        ]);

        $when = isset($validated['when']) ? Carbon::parse($validated['when']) : null;

        try {
            //Per default: Use the given station query for lookup
            $searchQuery = $validated['station'] ?? $validated['ibnr'];

            //If a station_id is given (=user is already on a stationboard) check if the user changed the query.
            //If so: Use the given station string. Otherwise, use the station_id for lookup.
            //This is to prevent that HAFAS fuzzy search return other stations (e.g. "Bern, Hauptbahnhof", Issue 1082)
            if (isset($validated['ibnr']) && $searchQuery !== $validated['ibnr']) {
                $station = HafasController::getTrainStation($validated['ibnr']);
                if ($station->name === $validated['station']) {
                    $searchQuery = $station->ibnr;
                }
            }
            $stationboardResponse = TransportBackend::getDepartures(
                stationQuery: $searchQuery,
                when:         $when,
                travelType:   TravelType::tryFrom($validated['travelType'] ?? null),
            );
            return view('stationboard', [
                                          'station'    => $stationboardResponse['station'],
                                          'departures' => $stationboardResponse['departures'],
                                          'times'      => $stationboardResponse['times'],
                                          'latest'     => TransportController::getLatestArrivals(Auth::user())
                                      ]
            );
        } catch (HafasException $exception) {
            report($exception);
            return back()->with('error', __('messages.exception.generalHafas'));
        } catch (ModelNotFoundException) {
            return redirect()->back()->with('error', __('controller.transport.no-station-found'));
        }
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
            'station'  => $nearestStation->ibnr,
            'provider' => 'train'
        ]);
    }

    public function TrainTrip(Request $request): Renderable|RedirectResponse {
        $validated = $request->validate([
                                            'tripID'          => ['required'],
                                            'lineName'        => ['required'],
                                            'start'           => ['required', 'numeric'],
                                            'departure'       => ['required', 'date'],
                                            'searchedStation' => ['nullable', 'exists:train_stations,id'],
                                        ]);

        try {
            $startStation = TrainStation::where('ibnr', $validated['start'])->firstOrFail();
            $departure    = Carbon::parse($validated['departure']);

            $hafasTrip = TrainCheckinController::getHafasTrip(
                $validated['tripID'],
                $validated['lineName'],
                $startStation->id,
            );

            $stopovers = $hafasTrip->stopoversNEW
                ->filter(function(TrainStopover $trainStopover) use ($departure): bool {
                    return $trainStopover->departure_planned->isAfter($departure);
                });

            // Find out where this train terminates and offer this as a "fast check-in" option.
            $lastStopover = $hafasTrip->stopoversNEW
                ->filter(function(TrainStopover $stopover) {
                    return !$stopover->isArrivalCancelled;
                })
                ->last();

            return view('trip', [
                'hafasTrip'       => $hafasTrip,
                'stopovers'       => $stopovers,
                'startStation'    => $startStation,
                'searchedStation' => isset($validated['searchedStation']) ? TrainStation::findOrFail($validated['searchedStation']) : null,
                'lastStopover'    => $lastStopover,
            ]);
        } catch (HafasException $exception) {
            return back()->with('error', $exception->getMessage());
        } catch (StationNotOnTripException) {
            return redirect()->back()->with('error', __('controller.transport.not-in-stopovers'));
        }
    }

    public function TrainCheckin(Request $request): RedirectResponse {
        $validated = $request->validate([
                                            'tripID'            => ['required'],
                                            'start'             => ['required', 'numeric'], //Origin station IBNR
                                            'departure'         => ['required', 'date'],
                                            'destination'       => ['required', 'numeric'], //Destination station IBNR
                                            'arrival'           => ['required', 'date'],
                                            'body'              => ['nullable', 'max:280'],
                                            'business_check'    => ['required', new Enum(Business::class)],
                                            'checkinVisibility' => ['nullable', new Enum(StatusVisibility::class)],
                                            'tweet_check'       => ['nullable', 'max:2'],
                                            'toot_check'        => ['nullable', 'max:2'],
                                            'chainPost_check'   => ['nullable', 'max:2'],
                                            'event'             => ['nullable', 'numeric', 'exists:events,id'],
                                            'force'             => ['nullable'],
                                        ]);

        try {
            $backendResponse = TrainCheckinController::checkin(
                user:         Auth::user(),
                hafasTrip:    HafasTrip::where('trip_id', $validated['tripID'])->first(),
                origin:       TrainStation::where('ibnr', $validated['start'])->first(),
                departure:    Carbon::parse($validated['departure']),
                destination:  TrainStation::where('ibnr', $validated['destination'])->first(),
                arrival:      Carbon::parse($validated['arrival']),
                travelReason: Business::from($validated['business_check']),
                visibility:   StatusVisibility::tryFrom($validated['checkinVisibility'] ?? StatusVisibility::PUBLIC->value),
                body:         $validated['body'] ?? null,
                event:        isset($validated['event']) ? Event::find($validated['event']) : null,
                force: isset($validated['force']),
                postOnMastodon: isset($request->toot_check),
                shouldChain: isset($request->chainPost_check),
            );

            $trainCheckin = $backendResponse['status']->trainCheckin;

            return redirect()->route('dashboard')->with('checkin-success', [
                'distance'                => $trainCheckin->distance,
                'duration'                => $trainCheckin->duration,
                'points'                  => $trainCheckin->points,
                'lineName'                => $trainCheckin->HafasTrip->linename,
                'alsoOnThisConnection'    => $trainCheckin->alsoOnThisConnection,
                'event'                   => $trainCheckin->event,
                'forced'                  => isset($validated['force']),
                'pointsCalculationReason' => $backendResponse['points']->reason,
            ]);

        } catch (CheckInCollisionException $exception) {
            return redirect()
                ->route('dashboard')
                ->with('checkin-collision', [
                    'lineName'  => $exception->getCollision()->HafasTrip->linename,
                    'validated' => $validated,
                ]);

        } catch (TrainCheckinAlreadyExistException) {
            return redirect()->route('dashboard')
                             ->with('error', __('messages.exception.general'));
        } catch (AlreadyCheckedInException) {
            $message = __('messages.exception.already-checkedin') . ' ' . __('messages.exception.maybe-too-fast');
            return redirect()->route('dashboard')
                             ->with('error', $message);
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
            $trainStation = HafasController::getStations(query: $validated['stationName'], results: 1)->first();
            if ($trainStation === null) {
                return redirect()->back()->with(['error' => __('messages.exception.general')]);
            }
            $trainStation = HomeController::setHome(auth()->user(), $trainStation);

            return redirect()->back()->with(['message' => __('user.home-set', ['station' => $trainStation->name])]);
        } catch (HafasException) {
            return redirect()->back()->with(['error' => __('messages.exception.generalHafas')]);
        }
    }

    private function isCancelled(mixed $param): bool {
        return $param['cancelled'] && $param['arrival'] == null && $param['departure'] == null;
    }
}
