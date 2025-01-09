<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Backend\Transport\StationController;
use App\Http\Controllers\Backend\User\DashboardController;
use App\Http\Controllers\Backend\User\ProfilePictureController;
use App\Http\Controllers\StatusController as StatusBackend;
use App\Models\Event;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

/**
 * @deprecated Content will be moved to the backend/frontend/API packages soon, please don't add new functions here!
 */
class FrontendStatusController extends Controller
{
    public function getDashboard(): Renderable|RedirectResponse {

        $statuses = DashboardController::getPrivateDashboard(auth()->user());

        return view('dashboard', [
            'statuses'         => $statuses,
            'latest'           => StationController::getLatestArrivals(auth()->user()),
            'future'           => StatusBackend::getFutureCheckins(),
            'showGlobalButton' => auth()->user()->follows->count() === 0
        ]);
    }

    public function getGlobalDashboard(): Renderable {
        return view('dashboard', [
            'statuses'         => DashboardController::getGlobalDashboard(Auth::user()),
            'latest'           => StationController::getLatestArrivals(Auth::user()),
            'future'           => StatusBackend::getFutureCheckins(),
            'showGlobalButton' => false
        ]);
    }

    public function getActiveStatuses(): View {
        return view('activejourneys', [
            'currentUser' => Auth::user(),
            'statuses'    => StatusBackend::getActiveStatuses(),
            'events'      => Event::forTimestamp(now())->get(),
            'event'       => null
        ]);
    }

    public function statusesByEvent(string $slug): Renderable {
        $event    = Event::where('slug', $slug)->firstOrFail();
        $response = StatusController::getStatusesByEvent($event);

        if ($response['event']->checkin_end->isPast() && $response['statuses']->count() === 0) {
            abort(404);
        }

        return view('eventsMap', [
            'statuses' => $response['statuses']->simplePaginate(15),
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

        return view('status', [
            'status'      => $status,
            'time'        => time(),
            'title'       => __('status.ogp-title', ['name' => $status->user->username]),
            'description' => trans_choice('status.ogp-description', preg_match('/\s/', $status->checkin->trip->linename), [
                'linename'    => $status->checkin->trip->linename,
                'distance'    => number($status->checkin->distance / 1000, 1),
                'destination' => $status->checkin->destinationStopover->station->name,
                'origin'      => $status->checkin->originStopover->station->name
            ]),
            'image'       => ProfilePictureController::getUrl($status->user),
        ]);
    }
}
