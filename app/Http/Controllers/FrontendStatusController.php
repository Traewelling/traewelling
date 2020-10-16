<?php

namespace App\Http\Controllers;

use App\Exceptions\StatusAlreadyLikedException;
use App\Models\Event;
use App\Http\Controllers\EventController as EventBackend;
use App\Http\Controllers\StatusController as StatusBackend;
use App\Models\Status;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class FrontendStatusController extends Controller
{
    public function getDashboard() {
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
            'latest'      => \App\Http\Controllers\TransportController::getLatestArrivals($user)
        ]);
    }

    public function getGlobalDashboard() {
        $statuses = StatusBackend::getGlobalDashboard();
        return view('dashboard', [
            'statuses'    => $statuses,
            'currentUser' => Auth::user(),
            'latest'      => \App\Http\Controllers\TransportController::getLatestArrivals(Auth::user())
        ]);
    }

    public function DeleteStatus(Request $request) {
        $deleteStatusResponse = StatusBackend::DeleteStatus(Auth::user(), $request['statusId']);
        if ($deleteStatusResponse === false) {
            return redirect()->back()->with('error', __('controller.status.not-permitted'));
        }
        return response()->json(['message' => __('controller.status.delete-ok')], 200);
    }

    public function EditStatus(Request $request) {
        $this->validate($request, [
            'body'          => 'max:280',
            'businessCheck' => 'max:1',
        ]);
        $editStatusResponse = StatusBackend::EditStatus(
            Auth::user(),
            $request['statusId'],
            $request['body'],
            $request['businessCheck']
        );
        if ($editStatusResponse === false) {
            return redirect()->back();
        }
        return response()->json(['new_body' => $editStatusResponse], 200);
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
        }
    }

    public function DestroyLike(Request $request) {
        $destroyLikeResponse = StatusBackend::DestroyLike(Auth::user(), $request['statusId']);
        if ($destroyLikeResponse === true) {
            return response(__('controller.status.like-deleted'), 200);
        }
        return response(__('controller.status.like-not-found'), 404);
    }

    public function exportLanding() {
        return view('export')->with([
                                        'begin_of_month' => (new \DateTime("first day of this month"))
                                            ->format("Y-m-d"),
                                        'end_of_month'   => (new \DateTime("last day of this month"))
                                            ->format("Y-m-d")
                                    ]);
    }

    public function export(Request $request) {
        $this->validate($request, [
            'begin'    => 'required|date|before_or_equal:end',
            'end'      => 'required|date|after_or_equal:begin',
            'filetype' => 'required|in:json,csv,pdf'
        ]);

        $export = StatusBackend::ExportStatuses($request->input('begin'),
                                                $request->input('end'),
                                                $request->input('filetype'));
        return $export;
    }

    public function getActiveStatuses() {
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

    public function statusesByEvent(string $event) {
        $events = Event::where('slug', '=', $event)->get();
        if ($events->count() == 0) {
            abort(404);
        }

        $e = $events->get(0);

        $statusesResponse = StatusBackend::getStatusesByEvent($e->id);

        return view('eventsMap', [
            'statuses'    => $statusesResponse,
            'events'      => $events,
            'event'       => $e,
            'currentUser' => Auth::user(),
        ]);
    }

    public function getStatus($statusId) {
        $statusResponse = StatusBackend::getStatus($statusId);

        return view('status', [
            'status' => $statusResponse,
            'time' => time(),
            'title' => __('status.ogp-title', ['name' => $statusResponse->user->username]),
            'description' => trans_choice('status.ogp-description', preg_match('/\s/', $statusResponse->trainCheckin->HafasTrip->linename), [
                'linename'    => $statusResponse->trainCheckin->HafasTrip->linename,
                'distance'    => $statusResponse->trainCheckin->distance,
                'destination' => $statusResponse->trainCheckin->Destination->name,
                'origin'      => $statusResponse->trainCheckin->Origin->name
            ]),
            'image' => route('account.showProfilePicture', ['username' => $statusResponse->user->username])
        ]);
    }

    public function usageboard(Request $request) {
        $begin = Carbon::now()->copy()->addDays(-14);
        $end   = Carbon::now();

        if ($request->input('begin') != "") {
            $begin = Carbon::createFromFormat("Y-m-d", $request->input('begin'));
        }
        if ($request->input('end') != "") {
            $end = Carbon::createFromFormat("Y-m-d", $request->input('end'));
        }

        if ($begin->isAfter($end)) {
            return redirect()
                ->back()
                ->with('error',
                       $begin->format('Y-m-d') .
                       ' ist vor ' .
                       $end->format('Y-m-d') .
                       '. Das darf nicht.');
        }
        if ($end->isFuture()) {
            $end = Carbon::now();
        }

        $dates                  = [];
        $statusesByDay          = [];
        $userRegistrationsByDay = [];
        $hafasTripsByDay        = [];

        // Wir schlagen einen Tag drauf, um ihn in der Loop direkt wieder runterzunehmen.
        $dateIterator = $end->copy()->addDays(1);
        $cnt          = 0;
        $datediff     = $end->diffInDays($begin);
        while ($cnt < $datediff) {
            $cnt++;
            $dateIterator->addDays(-1);
            $dates[]                  = $dateIterator->format("Y-m-d");
            $statusesByDay[]          = StatusController::usageByDay($dateIterator);
            $userRegistrationsByDay[] = UserController::registerByDay($dateIterator);

            // Wenn keine Status passiert sind, gibt es auch keine Möglichkeit, hafastrips anzulegen.
            if ($statusesByDay[count($statusesByDay) - 1] == 0) { // Heute keine Stati
                $hafasTripsByDay[] = (object) [];
            } else {
                $hafasTripsByDay[] = TransportController::usageByDay($dateIterator);
            }
        }

        if (empty($dates)) {
            $dates = [$begin->format("Y-m-d")];
        }

        return view('admin.usageboard', [
            'begin'                  => $begin->format("Y-m-d"),
            'end'                    => $end->format("Y-m-d"),
            'dates'                  => $dates,
            'statusesByDay'          => $statusesByDay,
            'userRegistrationsByDay' => $userRegistrationsByDay,
            'hafasTripsByDay'        => $hafasTripsByDay
        ]);
    }

    public static function nextStation(&$status) {
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
