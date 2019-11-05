<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\StatusController as StatusBackend;
use Illuminate\Support\Facades\Auth;

class FrontendStatusController extends Controller
{
    public function getDashboard() {
        $user = Auth::user();
        $statuses = StatusBackend::getDashboard($user);

        if (!$user->hasVerifiedEmail() && $user->email != null) {
            \Session::flash('message',
                            __('controller.status.email-not-verified',
                               ['url' => route('verification.resend')]
                            )
            );
        }
        if ($statuses->isEmpty()) {
            return redirect()->route('globaldashboard');
        }
        return view('dashboard', ['statuses' => $statuses]);
    }

    public function getGlobalDashboard() {
        $statuses = StatusBackend::getGlobalDashboard();
        return view('dashboard', ['statuses' => $statuses]);
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

    public function getActiveStatuses() {
        $ActiveStatusesResponse = StatusBackend::getActiveStatuses();
        return view('activejourneys', ['statuses' => $ActiveStatusesResponse['statuses'], 'polylines' => $ActiveStatusesResponse['polylines']]);
    }

    public function getStatus($id) {
        $StatusResponse = StatusBackend::getStatus($id);
        return view('status', ['status' => $StatusResponse]);
    }
}
