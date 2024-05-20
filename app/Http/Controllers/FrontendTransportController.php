<?php

namespace App\Http\Controllers;

use App\Dto\CheckinSuccess;
use App\Enum\Business;
use App\Enum\StatusVisibility;
use App\Enum\TravelType;
use App\Exceptions\Checkin\AlreadyCheckedInException;
use App\Exceptions\CheckInCollisionException;
use App\Exceptions\HafasException;
use App\Exceptions\StationNotOnTripException;
use App\Exceptions\TrainCheckinAlreadyExistException;
use App\Http\Controllers\Backend\Helper\StatusHelper;
use App\Http\Controllers\Backend\Transport\HomeController;
use App\Http\Controllers\Backend\Transport\StationController;
use App\Http\Controllers\Backend\Transport\TrainCheckinController;
use App\Http\Controllers\TransportController as TransportBackend;
use App\Models\Event;
use App\Models\Station;
use App\Models\Stopover;
use App\Models\Trip;
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
            $trainAutocompleteResponse = TransportBackend::getTrainStationAutocomplete($station);
            return response()->json($trainAutocompleteResponse);
        } catch (HafasException $e) {
            abort(503, $e->getMessage());
        }
    }

    public function TrainStationboard(Request $request): Renderable|RedirectResponse {
        $validated = $request->validate([
                                            'stationId'  => ['required_without_all:ibnr,station', 'numeric', 'exists:train_stations,id'],
                                            'station'    => ['required_without_all:ibnr,stationId'],
                                            'ibnr'       => ['required_without_all:station,stationId', 'numeric'],
                                            'when'       => ['nullable', 'date'],
                                            'travelType' => ['nullable', new Enum(TravelType::class)]
                                        ]);

        if (isset($validated['stationId'])) {
            $station = Station::findOrFail($validated['stationId']);
        } else {
            //Per default: Use the given station query for lookup
            $searchQuery = $validated['station'] ?? $validated['ibnr'];

            //If a station_id is given (=user is already on a stationboard) check if the user changed the query.
            //If so: Use the given station string. Otherwise, use the station_id for lookup.
            //This is to prevent that HAFAS fuzzy search return other stations (e.g. "Bern, Hauptbahnhof", Issue 1082)
            if (isset($validated['ibnr']) && $searchQuery !== $validated['ibnr']) {
                $station = HafasController::getStation($validated['ibnr']);
                if ($station->name === $validated['station']) {
                    $searchQuery = $station->ibnr;
                }
            }
            $station = StationController::lookupStation($searchQuery);
        }

        if ($request->user()->hasRole('open-beta')) {
            return redirect()->route('stationboard', ['stationId' => $station->id, 'stationName' => $station->name]);
        }

        $when = isset($validated['when'])
            ? Carbon::parse($validated['when'], auth()->user()->timezone ?? config('app.timezone'))
            : Carbon::now(auth()->user()->timezone ?? config('app.timezone'))->subMinutes(5);

        try {
            $stationboardResponse = HafasController::getDepartures(
                station:   $station,
                when:      $when,
                type:      TravelType::tryFrom($validated['travelType'] ?? null),
                localtime: true
            );

            return view('stationboard', [
                                          'station'    => $station,
                                          'departures' => $stationboardResponse,
                                          'times'      => [
                                              'now'  => $when,
                                              'prev' => $when->clone()->subMinutes(15),
                                              'next' => $when->clone()->addMinutes(15)
                                          ],
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
                $validatedInput['latitude'],
                $validatedInput['longitude'],
                1
            )->first();
        } catch (HafasException) {
            return back()->with('error', __('messages.exception.generalHafas'));
        }

        if ($nearestStation === null) {
            return back()->with('error', __('controller.transport.no-station-found'));
        }

        return redirect()->route('trains.stationboard', [
            'stationId' => $nearestStation->id,
            'provider'  => 'train'
        ]);
    }

    public function TrainTrip(Request $request): Renderable|RedirectResponse {
        $validated = $request->validate([
                                            'tripID'          => ['required'],
                                            'lineName'        => ['required'],
                                            'start'           => ['required', 'numeric'],
                                            'destination'     => ['nullable', 'numeric'],
                                            'departure'       => ['required', 'date'],
                                            'searchedStation' => ['nullable', 'exists:train_stations,id'],
                                        ]);

        if ($request->user()->hasRole('open-beta')) {
            return redirect()->route('stationboard', [
                'tripId'      => $validated['tripID'],
                'lineName'    => $validated['lineName'],
                'start'       => $validated['start'],
                'departure'   => $validated['departure'],
                'destination' => $validated['destination'] ?? null,
            ]);
        }

        try {
            $startStation = Station::where('ibnr', $validated['start'])->first();
            if ($startStation === null) {
                // in long term to support multiple data providers we only support IDs here - no IBNRs.
                $startStation = Station::findOrFail($validated['start']);
            }

            $trip = TrainCheckinController::getHafasTrip(
                $validated['tripID'],
                $validated['lineName'],
                $startStation->id,
            );

            $encounteredStart = false;
            $stopovers        = $trip->stopovers
                ->filter(function(Stopover $stopover) use ($startStation, &$encounteredStart): bool {
                    if (!$encounteredStart) { // this assumes stopovers being ordered correctly
                        $encounteredStart = $stopover->station->is($startStation);
                        return false;
                    }
                    return true;
                });

            // Find out where this train terminates and offer this as a "fast check-in" option.
            $lastStopover = $trip->stopovers
                ->filter(function(Stopover $stopover) {
                    return !$stopover->isArrivalCancelled;
                })
                ->last();

            return view('trip', [
                'hafasTrip'       => $trip,
                'stopovers'       => $stopovers,
                'startStation'    => $startStation,
                'searchedStation' => isset($validated['searchedStation']) ? Station::findOrFail($validated['searchedStation']) : null,
                'lastStopover'    => $lastStopover,
            ]);
        } catch (HafasException) {
            return redirect()->back()->with(['error' => __('messages.exception.generalHafas')]);
        } catch (StationNotOnTripException) {
            return redirect()->back()->with('error', __('controller.transport.not-in-stopovers'));
        }
    }

    public function TrainCheckin(Request $request): RedirectResponse {
        $validated = $request->validate([
                                            'tripID'            => ['required'],
                                            'start'             => ['required', 'numeric'], //origin station ID (or IBNR - in long term to support multiple data providers we only support IDs here)
                                            'departure'         => ['required', 'date'],
                                            'destination'       => ['required', 'numeric'], //Destination station ID (or IBNR - in long term to support multiple data providers we only support IDs here)
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
                trip:         Trip::where('trip_id', $validated['tripID'])->first(),
                origin:       Station::where('ibnr', $validated['start'])->first() ?? Station::findOrFail($validated['start']),
                departure:    Carbon::parse($validated['departure']),
                destination:  Station::where('ibnr', $validated['destination'])->first() ?? Station::findOrFail($validated['destination']),
                arrival:      Carbon::parse($validated['arrival']),
                travelReason: Business::from($validated['business_check']),
                visibility:   StatusVisibility::tryFrom($validated['checkinVisibility'] ?? StatusVisibility::PUBLIC->value),
                body:         $validated['body'] ?? null,
                event:        isset($validated['event']) ? Event::find($validated['event']) : null,
                force: isset($validated['force']),
                postOnMastodon: isset($request->toot_check),
                shouldChain: isset($request->chainPost_check),
            );

            $checkin = $backendResponse['status']->checkin;

            $checkinSuccess = new CheckinSuccess(
                id:                   $backendResponse['status']->id,
                distance:             $checkin->distance,
                duration:             $checkin->duration,
                points:               $checkin->points,
                pointReason:          $backendResponse['points']->reason,
                lineName:             $checkin->trip->linename,
                socialText:           StatusHelper::getSocialText($backendResponse['status']),
                alsoOnThisConnection: $checkin->alsoOnThisConnection,
                event:                $checkin->event,
                forced: isset($validated['force'])
            );
            return redirect()->route('status', ['id' => $backendResponse['status']->id])
                             ->with('checkin-success', (clone $checkinSuccess));

        } catch (CheckInCollisionException $exception) {
            return redirect()
                ->route('dashboard')
                ->with('checkin-collision', [
                    'lineName'  => $exception->getCollision()->trip->linename,
                    'validated' => $validated,
                ]);

        } catch (TrainCheckinAlreadyExistException) {
            return redirect()->route('dashboard')
                             ->with('error', __('messages.exception.already-checkedin'));
        } catch (AlreadyCheckedInException) {
            $message = __('messages.exception.already-checkedin') . ' ' . __('messages.exception.maybe-too-fast');
            return redirect()->route('dashboard')
                             ->with('error', $message);
        } catch (Throwable $exception) {
            report($exception);
            return redirect()
                ->route('dashboard')
                ->with('error', $exception->getMessage());
        }
    }

    public function setTrainHome(Request $request): RedirectResponse {
        $validated = $request->validate([
                                            'stationName' => ['required', 'max:255']
                                        ]);

        try {
            $station = HafasController::getStations(query: $validated['stationName'], results: 1)->first();
            if ($station === null) {
                return redirect()->back()->with(['error' => __('messages.exception.general')]);
            }
            $station = HomeController::setHome(auth()->user(), $station);

            return redirect()->back()->with(['success' => __('user.home-set', ['station' => $station->name])]);
        } catch (HafasException) {
            return redirect()->back()->with(['error' => __('messages.exception.generalHafas')]);
        }
    }
}
