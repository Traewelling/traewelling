<?php

namespace App\Http\Controllers;

use App\Enum\Business;
use App\Enum\StatusVisibility;
use App\Enum\TravelType;
use App\Exceptions\CheckInCollisionException;
use App\Exceptions\HafasException;
use App\Exceptions\TrainCheckinAlreadyExistException;
use App\Http\Controllers\Backend\EventController as EventBackend;
use App\Http\Controllers\Backend\Transport\HomeController;
use App\Http\Controllers\Backend\Transport\TrainCheckinController;
use App\Http\Controllers\TransportController as TransportBackend;
use App\Models\Event;
use App\Models\HafasTrip;
use App\Models\TrainStation;
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
                                            'station'    => ['required'],
                                            'when'       => ['nullable', 'date'],
                                            'travelType' => ['nullable', new Enum(TravelType::class)]
                                        ]);

        $when = isset($validated['when']) ? Carbon::parse($validated['when']) : null;

        try {
            $TrainStationboardResponse = TransportBackend::getDepartures(
                stationQuery: $validated['station'],
                when:         $when,
                travelType:   TravelType::tryFrom($validated['travelType'] ?? null),
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
            'station'  => $nearestStation->ibnr,
            'provider' => 'train'
        ]);
    }

    public function TrainTrip(Request $request): Renderable|RedirectResponse {
        $validated = $request->validate([
                                            'tripID'    => ['required'],
                                            'lineName'  => ['required'],
                                            'start'     => ['required', 'numeric'],
                                            'departure' => ['required', 'date']
                                        ]);

        try {
            $TrainTripResponse = TransportBackend::TrainTrip(
                $validated['tripID'],
                $validated['lineName'],
                $validated['start'],
                Carbon::parse($validated['departure'])
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
        $validated = $request->validate([
                                            'tripID'            => ['required'],
                                            'start'             => ['required', 'numeric'], //Origin station IBNR
                                            'departure'         => ['required', 'date'],
                                            'destination'       => ['required', 'numeric'], //Destination station IBNR
                                            'arrival'           => ['required', 'date'],
                                            'body'              => ['nullable', 'max:280'],
                                            'business_check'    => ['required', new Enum(Business::class)],
                                            'checkinVisibility' => ['nullable', new Enum(StatusVisibility::class)],
                                            'tweet_check'       => 'max:2',
                                            'toot_check'        => 'max:2',
                                            'event'             => ['nullable', 'numeric', 'exists:events,id'],
                                        ]);

        try {
            $backendResponse = TrainCheckinController::checkin(
                user:        Auth::user(),
                hafasTrip:   HafasTrip::where('trip_id', $validated['tripID'])->first(),
                origin:      TrainStation::where('ibnr', $validated['start'])->first(),
                departure:   Carbon::parse($validated['departure']),
                destination: TrainStation::where('ibnr', $validated['destination'])->first(),
                arrival:     Carbon::parse($validated['arrival']),
                tripType:    Business::from($validated['business_check']),
                visibility:  StatusVisibility::tryFrom($validated['checkinVisibility'] ?? StatusVisibility::PUBLIC->value),
                body:        $validated['body'] ?? null,
                event:       isset($validated['event']) ? Event::find($validated['event']) : null,
                // force:       false, //TODO
                postOnTwitter: isset($request->tweet_check),
                postOnMastodon: isset($request->toot_check)
            );

            $trainCheckin = $backendResponse['status']->trainCheckin;

            return redirect()->route('dashboard')->with('checkin-success', [
                'distance'             => $trainCheckin->distance,
                'duration'             => $trainCheckin->duration,
                'points'               => $trainCheckin->points,
                'lineName'             => $trainCheckin->HafasTrip->linename,
                'alsoOnThisConnection' => $trainCheckin->alsoOnThisConnection,
                'event'                => $trainCheckin->event
            ]);

        } catch (CheckInCollisionException $exception) {

            return redirect()
                ->route('dashboard')
                ->with('error', __(
                                    'controller.transport.overlapping-checkin',
                                    [
                                        'linename' => $exception->getCollision()->HafasTrip->linename
                                    ]
                                ) . strtr(' <a href=":url">#:id</a>',
                                          [
                                              ':url' => url('/status/' . $exception->getCollision()->status->id),
                                              ':id'  => $exception->getCollision()->status->id,
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
}
