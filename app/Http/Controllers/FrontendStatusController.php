<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Status;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

/**
 * @deprecated Content will be moved to the backend/frontend/API packages soon, please don't add new functions here!
 */
class FrontendStatusController extends Controller
{
    public function statusesByEvent(string $slug): View
    {
        $event = Event::where('slug', $slug)->firstOrFail();
        $response = StatusController::getStatusesByEvent($event);

        if ($response['event']->checkin_end->lt(today()) && $response['statuses']->count() === 0) {
            abort(404);
        }

        return view('vue.spa', [
            'title' => $event->name,
            'canonical' => route('event', ['slug' => $event->slug]),
        ]);
    }

    public function getStatus(int $statusId): View
    {
        $status = Status::find($statusId);
        $username = Gate::allows('view', $status) ? $status?->user->username : null;

        return view('vue.spa', [
            'title' => $username ? __('status.ogp-title', ['name' => $username]) : null,
        ]);
    }
}
