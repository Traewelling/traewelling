<?php

namespace App\Http\Controllers\Frontend\Admin;

use App\Events\StatusUpdateEvent;
use App\Http\Controllers\Backend\GeoController;
use App\Http\Controllers\Backend\Transport\PointsCalculationController;
use App\Http\Controllers\Controller;
use App\Models\Status;
use App\Models\TrainStation;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StatusEditController extends Controller
{
    public function renderMain(Request $request): View {
        $validated    = $request->validate([
                                               'userQuery' => ['nullable', 'max:255'],
                                           ]);
        $lastStatuses = Status::orderBy('created_at', 'desc')->limit(20);

        if (isset($validated['userQuery'])) {
            $lastStatuses = $lastStatuses->whereIn(
                'user_id',
                User::where('name', 'like', '%' . $validated['userQuery'] . '%')
                    ->orWhere('username', 'like', '%' . $validated['userQuery'] . '%')
                    ->pluck('id')
            );
        }

        return view('admin.status.main', [
            'lastStatuses' => $lastStatuses->get(),
        ]);
    }

    public function renderEdit(Request $request): View {
        $validated = $request->validate([
                                            'statusId' => ['required', 'exists:statuses,id'],
                                        ]);

        return view('admin.status.edit', [
            'status' => Status::find($validated['statusId'])
        ]);
    }

    public function edit(Request $request): RedirectResponse {
        $validated = $request->validate([
                                            'statusId'    => ['required', 'exists:statuses,id'],
                                            'origin'      => ['required', 'exists:train_stations,id'],
                                            'destination' => ['required', 'exists:train_stations,id'],
                                            'body'        => ['nullable', 'string'],
                                        ]);

        $status = Status::find($validated['statusId']);

        $originStation      = TrainStation::find($validated['origin']);
        $destinationStation = TrainStation::find($validated['destination']);

        $newOrigin      = $status->trainCheckIn->HafasTrip->stopoversNew->where('train_station_id', $originStation->id)->first();
        $newDestination = $status->trainCheckIn->HafasTrip->stopoversNew->where('train_station_id', $destinationStation->id)->first();

        $newDeparture = $newOrigin->departure_planned ?? $newOrigin->arrival_planned;
        $newArrival   = $newDestination->arrival_planned ?? $newDestination->departure_planned;

        $distanceInMeters = GeoController::calculateDistance(
            hafasTrip:   $status->trainCheckin->HafasTrip,
            origin:      $newOrigin,
            destination: $newDestination
        );

        $pointCalculation = PointsCalculationController::calculatePoints(
            distanceInMeter: $distanceInMeters,
            hafasTravelType: $status->trainCheckin->HafasTrip->category,
            departure:       $newDeparture,
            arrival:         $newArrival,
            timestampOfView: $newDeparture,
        );

        $status->trainCheckIn->update([
                                          'origin'      => $originStation->ibnr,
                                          'destination' => $destinationStation->ibnr,
                                          'departure'   => $newDeparture->toIso8601String(),
                                          'arrival'     => $newArrival->toIso8601String(),
                                          'distance'    => $distanceInMeters,
                                          'points'      => $pointCalculation->points,
                                      ]);

        StatusUpdateEvent::dispatch($status->refresh());

        if ($status->body !== $validated['body']) {
            $status->update(['body' => $validated['body']]);
        }

        return redirect()->route('admin.status')->with('alert-success', 'Der Status wurde bearbeitet.');
    }

}
