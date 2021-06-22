<?php

namespace App\Http\Controllers;

use App\Enum\StatusVisibility;
use App\Exceptions\PermissionException;
use App\Exceptions\StatusAlreadyLikedException;
use App\Http\Controllers\Backend\EventController as EventBackend;
use App\Http\Controllers\StatusController as StatusBackend;
use App\Models\Status;
use Carbon\Carbon;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Validation\Rule;
use InvalidArgumentException;
use Symfony\Component\HttpFoundation\StreamedResponse;

class FrontendStatusController extends Controller
{
    public function getDashboard(): Renderable|RedirectResponse {
        $user     = Auth::user();
        $statuses = StatusBackend::getDashboard($user);

        if (!$user->hasVerifiedEmail() && $user->email != null) {
            \Session::flash('mail-prompt', __('controller.status.email-not-verified'));
        }
        if ($statuses->isEmpty() || $user->follows->count() == 0) {
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
            'statuses'    => $statuses,
            'currentUser' => $user,
            'latest'      => TransportController::getLatestArrivals($user),
            'future'      => StatusBackend::getFutureCheckins()
        ]);
    }

    public function getGlobalDashboard(): Renderable {
        return view('dashboard', [
            'statuses'    => StatusBackend::getGlobalDashboard(),
            'currentUser' => Auth::user(),
            'latest'      => TransportController::getLatestArrivals(Auth::user()),
            'future'      => StatusBackend::getFutureCheckins()
        ]);
    }

    public function DeleteStatus(Request $request): JsonResponse|RedirectResponse {
        try {
            StatusBackend::DeleteStatus(Auth::user(), $request['statusId']);
        } catch (PermissionException | ModelNotFoundException) {
            return redirect()->back()->with('error', __('controller.status.not-permitted'));
        }

        return response()->json(['message' => __('controller.status.delete-ok')], 200);
    }

    public function EditStatus(Request $request): JsonResponse|RedirectResponse {
        $this->validate($request, [
            'body'              => ['max:280'],
            'business_check'    => ['required', 'digits_between:0,2'],
            'checkinVisibility' => ['required', Rule::in(StatusVisibility::getList())],
        ]);

        try {
            $editStatusResponse = StatusBackend::EditStatus(
                Auth::user(),
                $request['statusId'],
                $request['body'],
                $request['business_check'],
                $request['checkinVisibility']
            );
        } catch (ModelNotFoundException | PermissionException) {
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

    public function exportLanding(): Renderable {
        return view('export')->with([
                                        'begin_of_month' => Carbon::now()->firstOfMonth()->format('Y-m-d'),
                                        'end_of_month'   => Carbon::now()->lastOfMonth()->format('Y-m-d')
                                    ]);
    }

    public function export(Request $request): JsonResponse|StreamedResponse|Response {
        $validated = $request->validate([
                                            'begin'    => ['required', 'date', 'before_or_equal:end'],
                                            'end'      => ['required', 'date', 'after_or_equal:begin'],
                                            'filetype' => ['required', Rule::in(['json', 'csv', 'pdf'])],
                                        ]);

        return StatusBackend::ExportStatuses(
            startDate: Carbon::parse($validated['begin']),
            endDate: Carbon::parse($validated['end']),
            fileType: $request->input('filetype')
        );
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

        if ($response['event']->end->isPast() && $response['statuses']->count() == 0) {
            abort(404);
        }

        return view('eventsMap', [
            'statuses' => $response['statuses'],
            'event'    => $response['event']
        ]);
    }

    public function getStatus($statusId): Renderable {
        $statusResponse = StatusBackend::getStatus($statusId);

        return view('status', [
            'status'      => $statusResponse,
            'time'        => time(),
            'title'       => __('status.ogp-title', ['name' => $statusResponse->user->username]),
            'description' => trans_choice('status.ogp-description', preg_match('/\s/', $statusResponse->trainCheckin->HafasTrip->linename), [
                'linename'    => $statusResponse->trainCheckin->HafasTrip->linename,
                'distance'    => $statusResponse->trainCheckin->distance,
                'destination' => $statusResponse->trainCheckin->Destination->name,
                'origin'      => $statusResponse->trainCheckin->Origin->name
            ]),
            'image'       => route('account.showProfilePicture', ['username' => $statusResponse->user->username])
        ]);
    }

    /**
     * @param $status
     * @return mixed
     * @deprecated when vue is implemented
     */
    public static function nextStation(&$status) {
        if ($status->trainCheckin->HafasTrip->stopoversNEW->count() > 0) {
            return $status->trainCheckin->HafasTrip->stopoversNEW
                ->filter(function($stopover) {
                    return $stopover->arrival->isFuture();
                })->sortBy('arrival')
                ->first()?->trainStation?->name;
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
        return $stops[$nextStopIndex]->stop->name;
    }
}
