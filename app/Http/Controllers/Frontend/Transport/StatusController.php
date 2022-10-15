<?php

namespace App\Http\Controllers\Frontend\Transport;

use App\Enum\Business;
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

            if (isset($validated['destinationStopoverId'])
                && $validated['destinationStopoverId'] != $status->trainCheckin->destination_stopover->id) {
                TrainCheckinController::changeDestination(
                    checkin:                $status->trainCheckin,
                    newDestinationStopover: TrainStopover::findOrFail($validated['destinationStopoverId']),
                );
            }

            return redirect()->route('statuses.get', ['id' => $status->id])
                             ->with('success', __('status.update.success'));
        } catch (ModelNotFoundException|PermissionException) {
            return redirect()->back()->with('alert-danger', __('messages.exception.general'));
        }
    }
}
