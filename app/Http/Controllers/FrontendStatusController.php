<?php

namespace App\Http\Controllers;

use App\Exceptions\PermissionException;
use App\Exceptions\StatusAlreadyLikedException;
use App\Http\Controllers\Backend\EventController as EventBackend;
use App\Http\Controllers\Backend\GeoController;
use App\Http\Controllers\Backend\User\DashboardController;
use App\Http\Controllers\Backend\User\ProfilePictureController;
use App\Http\Controllers\StatusController as StatusBackend;
use App\Models\Status;
use App\Models\TrainStation;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
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

    public function createLike(Request $request) {
        $validated = $request->validate([
                                            'statusId' => ['required', 'exists:statuses,id']
                                        ]);

        try {
            $status = Status::findOrFail($validated['statusId']);
            StatusBackend::createLike(Auth::user(), $status);
            return response(__('controller.status.like-ok'), 201);
        } catch (StatusAlreadyLikedException) {
            return response(__('controller.status.like-already'), 409);
        } catch (PermissionException) {
            return response(__('controller.status.not-permitted'), 403);
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
        $activeStatusesResponse = StatusBackend::getActiveStatuses();
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
        } catch (AuthorizationException) {
            session(["extraLink" => [
                        'url'  => route('profile', ['username' => $status->user->username]),
                        'text' => "@" . $status->user->username]]
            );
            abort(403, __('error.status.not-authorized'));
        }

        //TODO: This is a temporary workaround. We should use standarised GeoJSON Format for this (see PR#629)
        if ($status?->trainCheckin?->HafasTrip?->polyline) {
            $polyline = GeoController::getMapLinesForCheckin($status->trainCheckin);
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
}
