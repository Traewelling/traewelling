<?php

namespace App\Http\Controllers;

use App\Enum\Business;
use App\Enum\StatusVisibility;
use App\Exceptions\PermissionException;
use App\Exceptions\StatusAlreadyLikedException;
use App\Http\Controllers\Backend\EventController as EventBackend;
use App\Http\Controllers\Backend\User\DashboardController;
use App\Http\Controllers\Backend\User\ProfilePictureController;
use App\Http\Controllers\StatusController as StatusBackend;
use App\Models\Status;
use App\Models\TrainStation;
use Carbon\Carbon;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Validation\Rules\Enum;
use InvalidArgumentException;

/**
 * @deprecated Content will be moved to the backend/frontend/API packages soon, please don't add new functions here!
 */
class FrontendStatusController extends Controller
{
    public function getDashboard(): Renderable|RedirectResponse {
        $statuses = DashboardController::getPrivateDashboard(auth()->user());

        if ($statuses->isEmpty() || auth()->user()->follows->count() == 0) {
            if (Session::has('checkin-success')) {
                return redirect()->route('globaldashboard')
                                 ->with('checkin-success', Session::get('checkin-success'));
            }
            if (Session::has('error')) {
                return redirect()->route('globaldashboard')
                                 ->with('error', Session::get('error'));
            }
            return redirect()->route('globaldashboard');
        }
        return view('dashboard', [
            'statuses' => $statuses,
            'latest'   => TransportController::getLatestArrivals(auth()->user()),
            'future'   => StatusBackend::getFutureCheckins()
        ]);
    }

    public function getGlobalDashboard(): Renderable {
        return view('dashboard', [
            'statuses' => DashboardController::getGlobalDashboard(Auth::user()),
            'latest'   => TransportController::getLatestArrivals(Auth::user()),
            'future'   => StatusBackend::getFutureCheckins()
        ]);
    }

    /**
     * @param Request $request
     *
     * @return JsonResponse|RedirectResponse
     * @todo Is this api? Because of JsonReponse. But if yes: Why it does an Redirect?
     */
    public function DeleteStatus(Request $request): JsonResponse|RedirectResponse {
        try {
            if (!is_numeric($request['statusId'])) {
                return redirect()->back()->with('error', __('error.bad-request'));
            }
            StatusBackend::DeleteStatus(Auth::user(), (int) $request['statusId']);
        } catch (PermissionException|ModelNotFoundException) {
            return redirect()->back()->with('error', __('controller.status.not-permitted'));
        }

        return response()->json(['message' => __('controller.status.delete-ok')]);
    }

    public function EditStatus(Request $request): JsonResponse|RedirectResponse {
        $validated = $this->validate($request, [
            'statusId'          => ['required', 'exists:statuses,id'],
            'body'              => ['nullable', 'max:280'],
            'business_check'    => ['required', new Enum(Business::class)],
            'checkinVisibility' => ['required', new Enum(StatusVisibility::class)],
        ]);

        try {
            $editStatusResponse = StatusBackend::EditStatus(
                user:       Auth::user(),
                statusId:   $validated['statusId'],
                body:       $validated['body'] ?? null,
                business:   Business::from($validated['business_check']),
                visibility: StatusVisibility::from($validated['checkinVisibility']),
            );
        } catch (ModelNotFoundException|PermissionException) {
            return redirect()->back();
        }

        return response()->json(['new_body' => $editStatusResponse->body], 200);
    }

    public function createLike(Request $request) {
        $validated = $request->validate([
                                            'statusId' => ['required', 'exists:statuses,id']
                                        ]);

        try {
            $status = Status::findOrFail($validated['statusId']);
            StatusBackend::createLike(Auth::user(), $status);
            return response(__('controller.status.like-ok'), 201);
        } catch (StatusAlreadyLikedException $e) {
            return response(__('controller.status.like-already'), 409);
        } catch (PermissionException) {
            abort(403);
        }
    }

    public function DestroyLike(Request $request): Response {
        try {
            StatusBackend::destroyLike(Auth::user(), $request['statusId']);
            return response(__('controller.status.like-deleted'));
        } catch (InvalidArgumentException $exception) {
            return response($exception->getMessage(), 404);
        }
    }

    public function getActiveStatuses(): Renderable {
        $activeStatusesResponse = StatusBackend::getActiveStatuses(null, false);
        $activeEvents           = EventBackend::activeEvents();
        return view('activejourneys', [
            'currentUser' => Auth::user(),
            'statuses'    => $activeStatusesResponse['statuses'],
            'polylines'   => $activeStatusesResponse['polylines'],
            'events'      => $activeEvents,
            'event'       => null
        ]);
    }

    public function statusesByEvent(string $event): Renderable {
        $response = StatusController::getStatusesByEvent($event, null);

        if ($response['event']->end->isPast() && $response['statuses']->count() === 0) {
            abort(404);
        }

        return view('eventsMap', [
            'statuses' => $response['statuses']->simplePaginate(15),
            'distance' => $response['distance'],
            'duration' => $response['duration'],
            'event'    => $response['event']
        ]);
    }

    public function getStatus($statusId): Renderable {
        $status = StatusBackend::getStatus($statusId);

        try {
            $this->authorize('view', $status);
        } catch (AuthorizationException $exception) {
            abort(403, $exception->getMessage());
        }

        //TODO: This is a temporary workaround. We should use standarised GeoJSON Format for this (see PR#629)
        if ($status?->trainCheckin?->HafasTrip?->polyline) {
            $polyline = $status->trainCheckin->getMapLines();
            foreach ($polyline as $element => $elementValue) {
                $polyline[$element] = [
                    $elementValue[1], $elementValue[0]
                ];
            }
        }

        return view('status', [
            'status'      => $status,
            'time'        => time(),
            'title'       => __('status.ogp-title', ['name' => $status->user->username]),
            'description' => trans_choice('status.ogp-description', preg_match('/\s/', $status->trainCheckin->HafasTrip->linename), [
                'linename'    => $status->trainCheckin->HafasTrip->linename,
                'distance'    => number($status->trainCheckin->distance / 1000, 1),
                'destination' => $status->trainCheckin->Destination->name,
                'origin'      => $status->trainCheckin->Origin->name
            ]),
            'image'       => ProfilePictureController::getUrl($status->user),
            'polyline'    => isset($polyline) ? json_encode($polyline, JSON_THROW_ON_ERROR) : null,
        ]);
    }

    /**
     * @param $status
     *
     * @return TrainStation|null
     * @deprecated when vue is implemented
     */
    public static function nextStation(&$status): ?TrainStation {
        if ($status->trainCheckin->HafasTrip->stopoversNEW->count() > 0) {
            return $status->trainCheckin->HafasTrip->stopoversNEW
                ->filter(function($stopover) {
                    return $stopover->arrival->isFuture();
                })->sortBy('arrival')
                ->first()?->trainStation;
        }

        $stops         = json_decode($status->trainCheckin->HafasTrip->stopovers);
        $nextStopIndex = count($stops) - 1;

        // Wir rollen die Reise von hinten auf, damit der nächste Stop als letztes vorkommt.
        for ($i = count($stops) - 1; $i > 0; $i--) {
            $arrival = Carbon::parse($stops[$i]->arrival);
            if ($arrival != null && $arrival->isFuture()) {
                $nextStopIndex = $i;
                continue;
            }
            break; // Wenn wir diesen Teil der Loop erreichen, kann die Loop beendert werden.
        }
        return $stops[$nextStopIndex]->stop;
    }
}
