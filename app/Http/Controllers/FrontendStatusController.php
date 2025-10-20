<?php

namespace App\Http\Controllers;

use App\Http\Controllers\StatusController as StatusBackend;
use App\Models\Event;
use App\Services\LicenseService;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

/**
 * @deprecated Content will be moved to the backend/frontend/API packages soon, please don't add new functions here!
 */
class FrontendStatusController extends Controller
{
    private LicenseService $licenseService;

    public function __construct(LicenseService $licenseService) {
        $this->licenseService = $licenseService;
    }

    public function statusesByEvent(string $slug): Renderable {
        $event    = Event::where('slug', $slug)->firstOrFail();
        $response = StatusController::getStatusesByEvent($event);

        if ($response['event']->checkin_end->isPast() && $response['statuses']->count() === 0) {
            abort(404);
        }

        return view('eventsMap', [
            'event' => $response['event']
        ]);
    }

    public function getStatus($statusId): View {
        $status = StatusBackend::getStatus($statusId);

        return view('beta.vue-status', [
            'statusId' => $statusId,
            'title'    => __('status.ogp-title', ['name' => $status->user->username]),
        ]);
    }
}
