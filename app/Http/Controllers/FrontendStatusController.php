<?php

namespace App\Http\Controllers;

use App\Enum\StatusVisibility;
use App\Models\Event;
use App\Models\Status;
use App\Services\ProfilePictureService;
use Illuminate\Http\RedirectResponse;
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

    public function redirectToStatus(int $statusId): RedirectResponse
    {
        $status = Status::findOrFail($statusId);

        return redirect()->route('status', ['uuid' => $status->uuid], 301);
    }

    public function getStatus(string $uuid): View
    {
        $status = Status::where('uuid', $uuid)->firstOrFail();
        $isVisible = Gate::allows('view', $status);
        $isIndexable = $status->visibility === StatusVisibility::PUBLIC
            && !$status->user->private_profile
            && !$status->user->prevent_index;

        return view('vue.spa', [
            'title' => $isVisible ? __('status.ogp-title', ['name' => $status->user->username]) : null,
            'canonical' => route('status', ['uuid' => $status->uuid]),
            'robots' => $isIndexable ? null : 'noindex',
            'description' => $isVisible ? $status->description : null,
            'image' => $isVisible ? resolve(ProfilePictureService::class)->getUrl($status->user) : null,
        ]);
    }
}
