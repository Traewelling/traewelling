<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Status;
use App\Services\LicenseService;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Support\Facades\Gate;
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

    public function getStatus(int $statusId): View {
        $status        = Status::find($statusId);
        $allowedToView = Gate::allows('view', $status);
        return view('single-status', [
            'statusId' => $statusId,
            'username' => $allowedToView ? $status?->user->username : null,
        ]);
    }
}
