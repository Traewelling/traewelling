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
use App\Http\Controllers\Backend\Transport\TrainCheckinController;
use App\Http\Controllers\HafasController;
use App\Http\Controllers\TransportController as TransportBackend;
use App\Jobs\PostStatusOnMastodon;
use App\Models\Event;
use App\Models\Status;
use App\Models\TrainStation;
use App\Models\TrainStopover;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Enum;
use Illuminate\View\View;
use Intervention\Image\Exception\NotFoundException;
use Throwable;

class CheckinController
{

    public function renderStationboard(Request $request): View|RedirectResponse {
        $validated = $request->validate([
                                            'station'   => ['nullable'],
                                            'when'      => ['nullable', 'date'],
                                            'filter'    => ['nullable', new Enum(TravelType::class)],
                                            'userQuery' => ['nullable']
                                        ]);

        $user = Auth::user();
        if (isset($validated['userQuery'])) {
            try {
                if (is_numeric($validated['userQuery'])) {
                    $user = User::findOrFail($validated['userQuery']);
                } else {
                    $user = User::where('username', 'like', '%' . $validated['userQuery'] . '%')->firstOrFail();
                }
            } catch (ModelNotFoundException) {
                return redirect()->back()->withErrors("User non-existent");
            }
        }

        $when = isset($validated['when']) ? Carbon::parse($validated['when']) : Carbon::now();

        if (isset($validated['station'])) {
            try {
                $trainStationboardResponse = TransportBackend::getDepartures(
                    stationQuery: $validated['station'],
                    when:         $when,
                    travelType:   TravelType::tryFrom($validated['filter'] ?? null),
                );

                $station    = $trainStationboardResponse['station'];
                $departures = $trainStationboardResponse['departures'];
                $times      = $trainStationboardResponse['times'];
            } catch (HafasException $exception) {
                return back()->with('error', $exception->getMessage());
            } catch (ModelNotFoundException) {
                return redirect()->back()->with('error', __('controller.transport.no-station-found'));
            }
        }

        $lastStatuses = Status::where('user_id', $user->id)->orderBy('created_at', 'desc')->limit(10)->get();

        return view('admin.checkin.stationboard', [
            'station'      => $station ?? null,
            'departures'   => $departures ?? null,
            'times'        => $times ?? null,
            'when'         => $when,
            'user'         => $user,
            'lastStatuses' => $lastStatuses,
        ]);
    }

    public function renderTrip(string $tripId, Request $request): RedirectResponse|View {
        $validated = $request->validate([
                                            'lineName'  => ['required'],
                                            'startIBNR' => ['required', 'numeric'],
                                            'departure' => ['required', 'date'],
                                            'userId'    => ['nullable', 'numeric']
                                        ]);

        $user = Auth::user();
        if (isset($validated['userId'])) {
            $user = User::find($validated['userId']);
        }

        try {
            $hafasTrip = TrainCheckinController::getHafasTrip(
                tripId:   $tripId,
                lineName: $validated['lineName'],
                startId:  $validated['startIBNR'],
            );
            return view('admin.checkin.trip', [
                'hafasTrip' => $hafasTrip,
                'events'    => EventBackend::activeEvents(),
                'stopovers' => $hafasTrip->stopoversNEW,
                'user'      => $user,
            ]);
        } catch (HafasException $exception) {
            return back()->with('alert-danger', $exception->getMessage());
        } catch (StationNotOnTripException) {
            return redirect()->back()->with('alert-danger', __('controller.transport.not-in-stopovers'));
        }
    }

    public function checkin(Request $request): View|RedirectResponse {
        $validated = $request->validate([
                                            'body'                => ['nullable', 'max:280'],
                                            'business'            => ['nullable', new Enum(Business::class)],
                                            'visibility'          => ['nullable', new Enum(StatusVisibility::class)],
                                            'eventId'             => ['nullable', 'integer', 'exists:events,id'],
                                            'toot'                => ['nullable', 'max:2'],
                                            'shouldChain_check'   => ['nullable', 'max:2'],
                                            'tripId'              => ['required'],
                                            'lineName'            => ['required'],
                                            'startIBNR'           => ['required', 'numeric'],
                                            'destinationStopover' => ['required', 'exists:train_stopovers,id'],
                                            'departure'           => ['required', 'date'],
                                            'force'               => ['nullable', 'max:2'],
                                            'userId'              => ['required', 'integer']
                                        ]);
        try {
            $user = User::findOrFail($validated['userId']);
        } catch (NotFoundException) {
            return redirect()->back()->withErrors('User non-existent');
        }

        $destinationStopover = TrainStopover::findOrFail($validated['destinationStopover']);

        try {
            $backendResponse = TrainCheckinController::checkin(
                user:         $user,
                hafasTrip:    HafasController::getHafasTrip($validated['tripId'], $validated['lineName']),
                origin:       TrainStation::where('ibnr', $validated['startIBNR'])->first(),
                departure:    Carbon::parse($validated['departure']),
                destination:  $destinationStopover->trainStation,
                arrival:      $destinationStopover->arrival_planned,
                travelReason: Business::tryFrom($validated['business'] ?? 0),
                visibility:   StatusVisibility::tryFrom($validated['visibility'] ?? 0),
                body:         $validated['body'] ?? null,
                event:        isset($validated['eventId']) ? Event::find($validated['eventId']) : null,
                force: isset($validated['force']),
                postOnMastodon: isset($request->toot_check),
                shouldChain: isset($request->shouldChain_check)
            );

            $status = $backendResponse['status'];

            PostStatusOnMastodon::dispatchIf(isset($validated['toot'])
                                             && $user?->socialProfile?->mastodon_id !== null,
                                             $status);

            return redirect()->route('admin.stationboard')
                             ->with('alert-success', 'Checked in successfully. Earned points: ' . $backendResponse['points']->points);

        } catch (CheckInCollisionException $e) {
            return redirect()
                ->back()
                ->withErrors(__(
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
            return back()->withErrors("station not on trip");
        } catch (HafasException $exception) {
            return back()->withErrors($exception->getMessage());
        } catch (TrainCheckinAlreadyExistException) {
            return back()->withErrors('CheckIn already exists');
        } catch (Throwable $throwed) {
            report($throwed);
            return back()->with('alert-danger', 'Fehler beim Speichern des CheckIns: ' . get_class($throwed) . ' -> ' . $throwed->getMessage());
        }
    }
}
