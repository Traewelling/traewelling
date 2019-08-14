<?php

namespace App\Http\Controllers;

use App\Like;
use App\Status;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;



class StatusController extends Controller
{
    public function getDashboard() {

        $statuses = Status::orderBy('created_at', 'desc')->get();
        return view('dashboard', ['statuses' => $statuses]);
    }

    public function CreateStatus(Request $request) {
        $this->validate($request, [
            'body' => 'required|max:140'
        ]);
        $status = new Status();
        $status->body = $request['body'];
        $message = 'There was an error.';
        if ($request->user()->statuses()->save($status)) {
            $message = 'Status successfully created!';
        }

        return redirect()->route('dashboard')->with(['message' => $message]);
    }

    public function DeleteStatus(Request $request) {
        $status = Status::find($request['statusId']);
        if (Auth::user() != $status->user) {
            return redirect()->back()->with('error', __('You \'re not permitted to do this'));
        }
        $status->delete();
        $status->trainCheckin()->delete();
        return response()->json(['message' => 'Status successfully deleted.'], 200);
    }

    public function EditStatus(Request $request) {
        $this->validate($request, [
            'body' => 'required'
        ]);
        $status = Status::find($request['statusId']);
        if (Auth::user() != $status->user) {
            return redirect()->back();
        }
        $status->body = $request['body'];
        $status->update();
        return response()->json(['new_body' => $status->body], 200);
    }

    public function createLike(Request $request) {
        $statusID = $request->statusId;
        $status = Status::find($statusID);

        if (!$status) {
            return 'no Status';
        }
        $user = Auth::user();
        $like = $user->likes()->where('status_id', $statusID)->first();

        if ($like) {
            return response('Like already exists', 409);
        }

        $like = new Like();
        $like->user_id = $user->id;
        $like->status_id = $status->id;
        $like->save();
        return response('Like created', 201);
    }

    public function destroyLike(Request $request) {
        $statusID = $request->statusId;
        $user = Auth::user();
        $like = $user->likes()->where('status_id', $statusID)->first();

        if ($like) {
            $like->delete();
            return response('Like deleted', 200);
        }

        return response('Like not found', 404);
    }

}
