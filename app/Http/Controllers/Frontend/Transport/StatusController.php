<?php

namespace App\Http\Controllers\Frontend\Transport;

use App\Dto\CheckinSuccess;
use App\Enum\Business;
use App\Enum\StatusVisibility;
use App\Events\StatusUpdateEvent;
use App\Http\Controllers\Backend\Helper\StatusHelper;
use App\Http\Controllers\Backend\Transport\TrainCheckinController;
use App\Http\Controllers\Controller;
use App\Models\Status;
use App\Models\Stopover;
use Carbon\Carbon;
use Illuminate\Auth\Access\AuthorizationException;
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
                                            'manualDeparture'       => ['nullable', 'date'],
                                            'manualArrival'         => ['nullable', 'date'],
                                            'business_check'        => ['required', new Enum(Business::class)], //TODO: Why is this not CamelCase?
                                            'checkinVisibility'     => ['required', new Enum(StatusVisibility::class)],
                                            'destinationStopoverId' => ['nullable', 'exists:train_stopovers,id'],
                                        ]);

        try {
            $status = Status::findOrFail($validated['statusId']);
            $this->authorize('update', $status);

            $newVisibility = StatusVisibility::from($validated['checkinVisibility']);

            //Check for disallowed status visibility changes
            if(auth()->user()->can('disallow-status-visibility-change') && $newVisibility != StatusVisibility::PRIVATE) {
                return back()->with('error', 'You are not allowed to update non-private statuses. Please set the status to private.');
            }

            $status->update([
                                'body'       => $validated['body'] ?? null,
                                'business'   => Business::from($validated['business_check']),
                                'visibility' => $newVisibility,
                            ]);

            $status->checkin->update([
                                         'manual_departure' => isset($validated['manualDeparture']) ?
                                             Carbon::parse($validated['manualDeparture'], auth()->user()->timezone) :
                                             null,
                                         'manual_arrival'   => isset($validated['manualArrival']) ?
                                             Carbon::parse($validated['manualArrival'], auth()->user()->timezone) :
                                             null,
                                     ]);

            StatusUpdateEvent::dispatch($status->refresh());

            if (isset($validated['destinationStopoverId'])
                && $validated['destinationStopoverId'] != $status->checkin->destinationStopover->id) {
                $pointReason = TrainCheckinController::changeDestination(
                    checkin:                $status->checkin,
                    newDestinationStopover: Stopover::findOrFail($validated['destinationStopoverId']),
                );
                $status->fresh();

                $checkinSuccess = new CheckinSuccess(
                    id:                   $status->id,
                    distance:             $status->checkin->distance,
                    duration:             $status->checkin->duration,
                    points:               $status->checkin->points,
                    pointReason:          $pointReason,
                    lineName:             $status->checkin->trip->linename,
                    socialText:           StatusHelper::getSocialText($status),
                    alsoOnThisConnection: $status->checkin->alsoOnThisConnection,
                    event:                $status->checkin->event,
                    forced:               false,
                    reason:               'status-updated'
                );

                return redirect()->route('status', ['id' => $status->id])
                                 ->with('checkin-success', (clone $checkinSuccess));
            }

            return redirect()->route('status', ['id' => $status->id])
                             ->with('success', __('status.update.success'));
        } catch (ModelNotFoundException) {
            return redirect()->back()->with('alert-danger', __('messages.exception.general'));
        } catch (AuthorizationException) {
            return redirect()->back()->with('alert-danger', __('error.status.not-authorized'));
        }
    }
}
