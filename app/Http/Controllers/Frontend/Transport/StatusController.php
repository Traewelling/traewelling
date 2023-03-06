<?php

namespace App\Http\Controllers\Frontend\Transport;

use App\Enum\Business;
use App\Enum\PointReason;
use App\Enum\StatusVisibility;
use App\Exceptions\PermissionException;
use App\Http\Controllers\Backend\Transport\TrainCheckinController;
use App\Http\Controllers\Controller;
use App\Models\Status;
use App\Models\TrainStopover;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules\Enum;

class StatusController extends Controller
{

    public function updateStatus(Request $request): JsonResponse|RedirectResponse {
        $validated = $request->validate([
                                            'statusId'              => ['required', 'exists:statuses,id'],
                                            'body'                  => ['nullable', 'max:280'],
                                            'real_departure'        => ['nullable', 'date'],
                                            'real_arrival'          => ['nullable', 'date'],
                                            'business_check'        => ['required', new Enum(Business::class)],
                                            'checkinVisibility'     => ['required', new Enum(StatusVisibility::class)],
                                            'destinationStopoverId' => ['nullable', 'exists:train_stopovers,id'],
                                        ]);

        try {
            $status = Status::findOrFail($validated['statusId']);
            $this->authorize('update', $status);
            $status->update([
                                'body'       => $validated['body'] ?? null,
                                'business'   => Business::from($validated['business_check']),
                                'visibility' => StatusVisibility::from($validated['checkinVisibility']),
                            ]);

            $status->trainCheckin->update([
                                              'real_departure' => $validated['real_departure'] ?? null,
                                              'real_arrival'   => $validated['real_arrival'] ?? null,
                                          ]);

            if (isset($validated['destinationStopoverId'])
                && $validated['destinationStopoverId'] != $status->trainCheckin->destination_stopover->id) {
                $pointReason = TrainCheckinController::changeDestination(
                    checkin:                $status->trainCheckin,
                    newDestinationStopover: TrainStopover::findOrFail($validated['destinationStopoverId']),
                );
                $status->fresh();

                return redirect()->route('statuses.get', ['id' => $status->id])
                                 ->with('checkin-success', [
                                     'reason'                  => 'status-updated',
                                     'distance'                => $status->trainCheckin->distance,
                                     'duration'                => $status->trainCheckin->duration,
                                     'points'                  => $status->trainCheckin->points,
                                     'lineName'                => $status->trainCheckin->HafasTrip->linename,
                                     'alsoOnThisConnection'    => $status->trainCheckin->alsoOnThisConnection,
                                     'event'                   => $status->trainCheckin->event,
                                     'forced'                  => false,
                                     'pointsCalculationReason' => $pointReason,
                                 ]);
            }

            return redirect()->route('statuses.get', ['id' => $status->id])
                             ->with('success', __('status.update.success'));
        } catch (ModelNotFoundException|PermissionException) {
            return redirect()->back()->with('alert-danger', __('messages.exception.general'));
        }
    }
}
