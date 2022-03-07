<?php

namespace App\Http\Controllers\Frontend\Admin;

use App\Enum\Business;
use App\Enum\StatusVisibility;
use App\Enum\TravelType;
use App\Exceptions\CheckInCollisionException;
use App\Exceptions\HafasException;
use App\Exceptions\StationNotOnTripException;
use App\Exceptions\TrainCheckinAlreadyExistException;
use App\Http\Controllers\Backend\EventController as EventBackend;
use App\Http\Controllers\Backend\Social\MastodonController;
use App\Http\Controllers\Backend\Social\TwitterController;
use App\Http\Controllers\Backend\Transport\TrainCheckinController;
use App\Http\Controllers\HafasController;
use App\Http\Controllers\StatusController as StatusBackend;
use App\Http\Controllers\TransportController as TransportBackend;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Enum;
use Illuminate\View\View;

class CheckinController
{

    public function renderStationboard(Request $request): View|RedirectResponse {
        $validated = $request->validate([
                                            'station' => ['nullable'],
                                            'when'    => ['nullable', 'date'],
                                            'filter'  => ['nullable', new Enum(TravelType::class)],
                                            'user'    => ['nullable', 'numeric']
                                        ]);

        $user = Auth::user();
        if (isset($request->user)) {
            $user = User::find($request->user);
        }

        $when = isset($validated['when']) ? Carbon::parse($validated['when']) : Carbon::now();

        $station = $departures = $times = null;

        if (isset($validated['station'])) {
            try {
                $TrainStationboardResponse = TransportBackend::getDepartures(
                    stationQuery: $validated['station'],
                    when:         $when,
                    travelType:   TravelType::tryFrom($validated['filter'] ?? null),
                );

                $station    = $TrainStationboardResponse['station'];
                $departures = $TrainStationboardResponse['departures'];
                $times      = $TrainStationboardResponse['times'];
            } catch (HafasException $exception) {
                return back()->with('error', $exception->getMessage());
            } catch (ModelNotFoundException) {
                return redirect()->back()->with('error', __('controller.transport.no-station-found'));
            }
        }

        return view('admin.checkin.stationboard', [
            'station'    => $station,
            'departures' => $departures,
            'times'      => $times,
            'when'       => $when,
            'user'       => $user
        ]);
    }

    public function renderTrip(string $id, Request $request) {


        $request->validate([
                               'lineName'  => ['required'],
                               'start'     => ['required', 'numeric'],
                               'departure' => ['required', 'date'],
                               'user'      => ['nullable', 'numeric']
                           ]);

        $user = Auth::user();
        if (isset($request->user)) {
            $user = User::find($request->user);
        }

        try {
            $TrainTripResponse = TransportBackend::TrainTrip(
                $id,
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

        return view('admin.checkin.trip', [
            'hafasTrip'    => $TrainTripResponse['hafasTrip'],
            'destination'  => $TrainTripResponse['destination'], //deprecated. use hafasTrip->destinationStation instead
            'events'       => EventBackend::activeEvents(),
            'start'        => $TrainTripResponse['start'], //deprecated. use hafasTrip->originStation instead
            'stopovers'    => $TrainTripResponse['stopovers'],
            'terminalStop' => $terminalStop,
            'user'         => $user,
        ]);
    }

    public function checkin(Request $request) {


        $validated = $request->validate([
                                            'body'        => ['nullable', 'max:280'],
                                            'business'    => ['nullable', new Enum(Business::class)],
                                            'visibility'  => ['nullable', new Enum(StatusVisibility::class)],
                                            'eventId'     => ['nullable', 'integer', 'exists:events,id'],
                                            'tweet'       => ['nullable', 'boolean'],
                                            'toot'        => ['nullable', 'boolean'],
                                            'tripId'      => ['required'],
                                            'lineName'    => ['required'],
                                            'start'       => ['required', 'numeric'],
                                            'destination' => ['required', 'json'],
                                            'departure'   => ['required', 'date'],
                                            'force'       => ['nullable', 'boolean'],
                                            'user'        => ['required', 'integer']
                                        ]);

        $user = User::findOrFail($request->user);

        $destination = json_decode($request->destination, true);

        try {
            $status = StatusBackend::createStatus(
                user:       $user,
                business:   Business::tryFrom($validated['business'] ?? 0),
                visibility: StatusVisibility::tryFrom($validated['visibility'] ?? 0),
                body:       $validated['body'] ?? null,
                eventId:    $validated['eventId'] ?? null
            );

            $hafasTrip = HafasController::getHafasTrip($validated['tripId'], $validated['lineName']);

            $trainCheckinResponse = TrainCheckinController::createTrainCheckin(
                status:    $status,
                trip:      $hafasTrip,
                entryStop: $validated['start'],
                exitStop:  $destination['destination'],
                departure: Carbon::parse($validated['departure']),
                arrival:   Carbon::parse($destination['arrival']),
                force:     $validated['force'] ?? false,
                ibnr:      true
            );

            if (isset($validated['tweet']) && $user?->socialProfile?->twitter_id != null) {
                TwitterController::postStatus($status);
            }
            if (isset($validated['toot']) && $user?->socialProfile?->mastodon_id != null) {
                MastodonController::postStatus($status);
            }

            return redirect()->route('statuses.get', ['id' => $trainCheckinResponse['status']['id']])
                             ->with('success', 'points: ' . $trainCheckinResponse['points']['points']);

        } catch (CheckInCollisionException $e) {
            $status?->delete();
            return redirect()
                ->back()
                ->withErrors( __(
                                    'controller.transport.overlapping-checkin',
                                    [
                                        'linename' => $e->getCollision()->HafasTrip->linename
                                    ]
                                ) . strtr(' <a href=":url">#:id</a>',
                                          [
                                              ':url' => url('/status/' . $e->getCollision()->status->id),
                                              ':id'  => $e->getCollision()->status->id,
                                          ]
                                ));

        } catch (StationNotOnTripException) {
            $status?->delete();
            return redirect()
                ->back()
                ->withErrors("station not on trip");
        } catch (HafasException $exception) {
            $status?->delete();
            return redirect()
                ->back()
                ->withErrors($exception->getMessage());
        } catch (TrainCheckinAlreadyExistException) {
            return redirect()
                ->back()
                ->withErrors('CheckIn already exists');
        }
    }
}
