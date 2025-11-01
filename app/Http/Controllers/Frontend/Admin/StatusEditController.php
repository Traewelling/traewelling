<?php

namespace App\Http\Controllers\Frontend\Admin;

use App\Enum\StatusVisibility;
use App\Events\StatusUpdateEvent;
use App\Http\Controllers\Backend\Support\LocationController;
use App\Http\Controllers\Backend\Transport\PointsCalculationController;
use App\Http\Controllers\Backend\Transport\TrainCheckinController;
use App\Http\Controllers\Controller;
use App\Models\Station;
use App\Models\Status;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules\Enum;
use Illuminate\View\View;

class StatusEditController extends Controller
{
    public function index(Request $request): View {
        $validated    = $request->validate([
                                               'userQuery' => ['nullable', 'max:255'],
                                           ]);
        $lastStatuses = Status::orderBy('created_at', 'desc');

        if (isset($validated['userQuery'])) {
            $lastStatuses = $lastStatuses->whereIn(
                'user_id',
                User::where('name', 'like', '%' . $validated['userQuery'] . '%')
                    ->orWhere('username', 'like', '%' . $validated['userQuery'] . '%')
                    ->pluck('id')
            );
        }

        return view('admin.statuses.index', [
            'lastStatuses' => $lastStatuses->paginate(10),
        ]);
    }

    public function find(Request $request): RedirectResponse {
        $validated = $request->validate([
                                            'statusId' => ['required', 'integer', 'exists:statuses,id'],
                                        ]);

        return redirect()->route('admin.statuses.edit', ['statusId' => $validated['statusId']]);
    }

    public function renderEdit(int $statusId, Request $request): View {
        return view('admin.statuses.edit', [
            'status' => Status::findOrFail($statusId)
        ]);
    }

    public function edit(int $statusId, Request $request): RedirectResponse {
        $validated = $request->validate([
                                            'origin'           => ['required', 'exists:train_stations,id'],
                                            'destination'      => ['required', 'exists:train_stations,id'],
                                            'body'             => ['nullable', 'string'],
                                            'visibility'       => ['required', new Enum(StatusVisibility::class)],
                                            'event_id'         => ['nullable', 'integer', 'exists:events,id'],
                                            'points'           => ['nullable', 'integer', 'gte:0'], //if null, points will be recalculated
                                            'moderation_notes' => ['nullable', 'string', 'max:255'],
                                            'lock_visibility'  => ['nullable', 'boolean'],
                                            'hide_body'        => ['nullable', 'boolean'],
                                        ]);

        $status = Status::findOrFail($statusId);

        $originStation      = Station::find($validated['origin']);
        $destinationStation = Station::find($validated['destination']);

        $newOrigin      = $status->checkin->trip->stopovers->where('train_station_id', $originStation->id)->first();
        $newDestination = $status->checkin->trip->stopovers->where('train_station_id', $destinationStation->id)->first();

        $newDeparture = $newOrigin->departure_planned ?? $newOrigin->arrival_planned;
        $newArrival   = $newDestination->arrival_planned ?? $newDestination->departure_planned;

        $distanceInMeters = (new LocationController(
            trip:        $status->checkin->trip,
            origin:      $newOrigin,
            destination: $newDestination
        ))->calculateDistance();

        $pointCalculation = PointsCalculationController::calculatePoints(
            distanceInMeter: $distanceInMeters,
            hafasTravelType: $status->checkin->trip->category,
            departure:       $newDeparture,
            arrival:         $newArrival,
            tripSource:      $status->checkin->trip->source,
            timestampOfView: $newDeparture,
        );

        $status->checkin->update([
                                     'origin'                  => $originStation->ibnr,
                                     'origin_stopover_id'      => $newOrigin->id,
                                     'destination'             => $destinationStation->ibnr,
                                     'destination_stopover_id' => $newDestination->id,
                                     'departure'               => $newDeparture,
                                     'arrival'                 => $newArrival,
                                     'distance'                => $distanceInMeters,
                                     'points'                  => $validated['points'] ?? $pointCalculation->points,
                                     'duration'                => TrainCheckinController::calculateCheckinDuration(
                                         $status->checkin,
                                         false
                                     ),
                                 ]);

        StatusUpdateEvent::dispatch($status->refresh());

        $payload = [
            'visibility'       => $validated['visibility'],
            'event_id'         => $validated['event_id'],
            'moderation_notes' => null,
        ];

        if ($status->body !== $validated['body']) {
            $payload['body'] = $validated['body'];
        }
        if (isset($validated['moderation_notes'])) {
            $payload['moderation_notes'] = $validated['moderation_notes'];
        }
        if (isset($validated['lock_visibility'])) {
            $payload['lock_visibility'] = $validated['lock_visibility'];
        }
        if (isset($validated['hide_body'])) {
            $payload['hide_body'] = $validated['hide_body'];
        }

        $status->update($payload);

        return redirect()->route('admin.statuses.edit', ['statusId' => $status->id])
                         ->with('alert-success', 'Status successfully updated');
    }

}
