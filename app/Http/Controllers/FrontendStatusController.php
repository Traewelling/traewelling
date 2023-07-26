<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Backend\EventController as EventBackend;
use App\Http\Controllers\Backend\GeoController;
use App\Http\Controllers\Backend\User\DashboardController;
use App\Http\Controllers\Backend\User\ProfilePictureController;
use App\Http\Controllers\StatusController as StatusBackend;
use App\Models\Event;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

/**
 * @deprecated Content will be moved to the backend/frontend/API packages soon, please don't add new functions here!
 */
class FrontendStatusController extends Controller
{
    public function getDashboard(): Renderable|RedirectResponse {
        $statuses = DashboardController::getPrivateDashboard(auth()->user());

        if ($statuses->isEmpty() || auth()->user()->follows->count() === 0) {
            if (Session::has('checkin-success')) {
                return redirect()->route('globaldashboard')
                                 ->with('checkin-success', Session::get('checkin-success'));
            }
            if (Session::has('error')) {
                return redirect()->route('globaldashboard')
                                 ->with('error', Session::get('error'));
            }
            if (Session::has('checkin-collision')) {
                return redirect()->route('globaldashboard')
                                 ->with('checkin-collision', Session::get('checkin-collision'));
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

    public function statusesByEvent(string $slug): Renderable {
        $event    = Event::where('slug', $slug)->firstOrFail();
        $response = StatusController::getStatusesByEvent($event);

        if ($response['event']->end->isPast() && $response['statuses']->count() === 0) {
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

        //TODO: This is a temporary workaround. We should use standarised GeoJSON Format for this (see PR#629)
        if ($status->trainCheckin?->HafasTrip?->polyline) {
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
                'destination' => $status->trainCheckin->destinationStation->name,
                'origin'      => $status->trainCheckin->originStation->name
            ]),
            'image'       => ProfilePictureController::getUrl($status->user),
            'polyline'    => isset($polyline) ? json_encode($polyline, JSON_THROW_ON_ERROR) : null,
        ]);
    }
}
