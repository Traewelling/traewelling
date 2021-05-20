<?php

namespace App\Http\Controllers;

use App\Enum\TravelType;
use App\Exceptions\CheckInCollisionException;
use App\Exceptions\HafasException;
use App\Http\Controllers\EventController as EventBackend;
use App\Http\Controllers\TransportController as TransportBackend;
use Carbon\Carbon;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Throwable;

class FrontendTransportController extends Controller
{
    public function TrainAutocomplete($station): JsonResponse {
        $TrainAutocompleteResponse = TransportBackend::TrainAutocomplete($station);
        return response()->json($TrainAutocompleteResponse);
    }

    public function BusAutocomplete($station): JsonResponse {
        $BusAutocompleteResponse = TransportBackend::BusAutocomplete($station);
        return response()->json($BusAutocompleteResponse);
    }

    public function TrainStationboard(Request $request): Renderable|RedirectResponse {

        $validated = $request->validate([
                                            'station'    => ['required', 'string'],
                                            'when'       => ['nullable', 'date'],
                                            'travelType' => ['nullable', Rule::in(TravelType::getList())]
                                        ]);

        $when = isset($validated['when']) ? Carbon::parse($validated['when']) : null;

        try {
            $TrainStationboardResponse = TransportBackend::TrainStationboard(
                $validated['station'],
                $when,
                $validated['travelType'] ?? null
            );
        } catch (HafasException $exception) {
            return back()->with('error', $exception->getMessage());
        }
        if ($TrainStationboardResponse === false) {
            return redirect()->back()->with('error', __('controller.transport.no-name-given'));
        }
        if ($TrainStationboardResponse === null) {
            return redirect()->back()->with('error', __('controller.transport.no-station-found'));
        }

        return view('stationboard', [
                                      'station'    => $TrainStationboardResponse['station'],
                                      'departures' => $TrainStationboardResponse['departures'],
                                      'when'       => $TrainStationboardResponse['when'],
                                      'request'    => $request,
                                      'latest'     => TransportController::getLatestArrivals(Auth::user())
                                  ]
        );
    }

    public function StationByCoordinates(Request $request): RedirectResponse {
        $validatedInput = $request->validate([
                                                 'latitude'  => 'required|numeric|min:-180|max:180',
                                                 'longitude' => 'required|numeric|min:-180|max:180'
                                             ]);

        $nearestStation = HafasController::getNearbyStations($validatedInput['latitude'], $validatedInput['longitude'], 1)->first();
        if ($nearestStation === null) {
            return redirect()->back()->with('error', __('controller.transport.no-station-found'));
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
            'body'           => 'max:280',
            'business_check' => 'digits_between:0,2',
            'tweet_check'    => 'max:2',
            'toot_check'     => 'max:2',
            'event'          => 'integer',
            'departure'      => ['required', 'date'],
            'arrival'        => ['required', 'date'],
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

        } catch (Throwable $exception) {
            report($exception);
            return redirect()
                ->route('dashboard')
                ->with('error', __('messages.exception.general'));

        }

    }

    public function setHome(Request $request): RedirectResponse {
        $validated = $request->validate([
                                            'ibnr' => ['required', 'numeric']
                                        ]);

        try {
            $trainStation = TransportBackend::setHome(Auth::user(), $validated['ibnr']);

            return redirect()->back()->with(['message' => __('user.home-set', ['station' => $trainStation->name])]);
        } catch (HafasException $e) {
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
