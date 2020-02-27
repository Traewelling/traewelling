<?php

namespace App\Http\Controllers;

use App\Event;
use App\Http\Controllers\EventController as EventBackend;
use App\Http\Controllers\StatusController as StatusBackend;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class FrontendStatusController extends Controller
{
    public function getDashboard() {
        $user = Auth::user();
        $follows = $user->follows()->get();
        $statuses = StatusBackend::getDashboard($user);

        if (!$user->hasVerifiedEmail() && $user->email != null) {
            \Session::flash('message',
                            __('controller.status.email-not-verified',
                               ['url' => route('verification.resend')]
                            )
            );
        }
        if ($statuses->isEmpty() || $follows->isEmpty()) {
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
            'currentUser' => $user,
            'latest' => \App\Http\Controllers\TransportController::getLatestArrivals($user)
        ]);
    }

    public function getGlobalDashboard() {
        $statuses = StatusBackend::getGlobalDashboard();
        return view('dashboard', [
            'statuses' => $statuses,
            'currentUser' => Auth::user(),
            'latest' => \App\Http\Controllers\TransportController::getLatestArrivals(Auth::user())
        ]);
    }

    public function DeleteStatus(Request $request) {
        $DeleteStatusResponse = StatusBackend::DeleteStatus(Auth::user(), $request['statusId']);
        if ($DeleteStatusResponse === false) {
            return redirect()->back()->with('error', __('controller.status.not-permitted'));
        }
        return response()->json(['message' => __('controller.status.delete-ok')], 200);
    }

    public function EditStatus(Request $request) {
        $this->validate($request, [
            'body' => 'max:280',
            'businessCheck' => 'max:1',
        ]);
        $EditStatusResponse = StatusBackend::EditStatus(
            Auth::user(),
            $request['statusId'],
            $request['body'],
            $request['businessCheck']
        );
        if ($EditStatusResponse === false) {
            return redirect()->back();
        }
        return response()->json(['new_body' => $EditStatusResponse], 200);
    }

    public function CreateLike(Request $request) {
        $CreateLikeResponse = StatusBackend::CreateLike(Auth::user(), $request['statusId']);
        if ($CreateLikeResponse === null) {
            return response(__('controller.status.status-not-found'), 404);
        }
        if ($CreateLikeResponse === false) {
            return response(__('controller.status.like-already'), 409);
        }
        return response(__('controller.status.like-ok'), 201);
    }

    public function DestroyLike(Request $request) {
        $DestroyLikeResponse = StatusBackend::DestroyLike(Auth::user(), $request['statusId']);
        if ($DestroyLikeResponse === true) {
            return response(__('controller.status.like-deleted'), 200);
        }
        return response(__('controller.status.like-not-found'), 404);
    }

    public function exportLanding() {
        return view('export')->with([
            'begin_of_month' => (new \DateTime("first day of this month"))
                ->format("Y-m-d"),
            'end_of_month' => (new \DateTime("last day of this month"))
                ->format("Y-m-d")
        ]);
    }

    public function export(Request $request) {
        $this->validate($request, [
            'begin' => 'required|date|before_or_equal:end',
            'end' => 'required|date|after_or_equal:begin',
            'filetype' => 'required|in:json,csv,pdf'
        ]);

        $export = StatusBackend::ExportStatuses($request->input('begin'), $request->input('end'), $request->input('filetype'));
        return $export;
    }

    public function getActiveStatuses() {
        $ActiveStatusesResponse = StatusBackend::getActiveStatuses();
        $ActiveEvents = EventBackend::activeEvents();
        return view('activejourneys', [
            'statuses' => $ActiveStatusesResponse['statuses'],
            'polylines' => $ActiveStatusesResponse['polylines'],
            'events' => $ActiveEvents,
            'event' => null
        ]);
    }

    public function statusesByEvent(String $event) {
        $events = Event::where('slug', '=', $event)->get();
        if($events->count() == 0) {
            abort(404);
        }

        $e = $events->get(0);

        $statusesResponse = StatusBackend::getStatusesByEvent($e->id);

        return view('eventsMap', [
            'statuses' => $statusesResponse,
            'events' => $events,
            'event' => $e,
            'currentUser' => Auth::user(),
        ]);
    }

    public function getStatus($id) {
        $StatusResponse = StatusBackend::getStatus($id);

        $t = time();

        return view('status', [
            'status' => $StatusResponse,
            'currentUser' => Auth::user(),
            'time' => $t,
            'title' => __('status.ogp-title', ['name' => $StatusResponse->user->username]),
            'description' => trans_choice('status.ogp-description', preg_match('/\s/', $StatusResponse->trainCheckin->HafasTrip->linename), [
                'linename' => $StatusResponse->trainCheckin->HafasTrip->linename,
                'distance' => $StatusResponse->trainCheckin->distance,
                'destination' => $StatusResponse->trainCheckin->Destination->name,
                'origin' => $StatusResponse->trainCheckin->Origin->name
            ]),
            'image' => route('account.showProfilePicture', ['username' => $StatusResponse->user->username]),
            'dtObj' => new \DateTime($StatusResponse->trainCheckin->departure),
        ]);
    }

    public function usageboard(Request $request) {
        $begin = Carbon::now()->copy()->addDays(-14);
        $end = Carbon::now();

        if($request->input('begin') != "") {
            $begin = Carbon::createFromFormat("Y-m-d", $request->input('begin'));
        }
        if($request->input('end') != "") {
            $end = Carbon::createFromFormat("Y-m-d", $request->input('end'));
        }

        if($begin->isAfter($end)) {
            return redirect()->back()->with('error', $begin->format('Y-m-d') . ' ist vor ' . $end->format('Y-m-d') . '. Das darf nicht.');
        }
        if($end->isFuture()) {
            $end = Carbon::now();
        }

        $dates = [];
        $statusesByDay = [];
        $userRegistrationsByDay = [];
        $hafasTripsByDay = [];

        // Wir schlagen einen Tag drauf, um ihn in der Loop direkt wieder runterzunehmen.
        $dateIterator = $end->copy()->addDays(1);
        $i = 0; $datediff = $end->diffInDays($begin);
        while($i < $datediff) {
            $i++;
            $dateIterator->addDays(-1);
            $dates[] = $dateIterator->format("Y-m-d");

            $statusesByDay[] = StatusController::usageByDay($dateIterator);
            $userRegistrationsByDay[] = UserController::registerByDay($dateIterator);
            // Wenn keine Stati passiert sind, gibt es auch keine Möglichkeit, hafastrips anzulegen.
            if($statusesByDay[count($statusesByDay) - 1] == 0) { // Heute keine Stati
                $hafasTripsByDay[] = (object) [];
            } else {
                $hafasTripsByDay[] = TransportController::usageByDay($dateIterator);
            }
        }

        if(empty($dates)) {
            $dates = [$begin->format("Y-m-d")];
        }

        return view('admin.usageboard', [
            'begin' => $begin->format("Y-m-d"),
            'end' => $end->format("Y-m-d"),
            'dates' => $dates,
            'statusesByDay' => $statusesByDay,
            'userRegistrationsByDay' => $userRegistrationsByDay,
            'hafasTripsByDay' => $hafasTripsByDay
        ]);
    }
}
