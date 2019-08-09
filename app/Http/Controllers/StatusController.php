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
            return redirect()->back();
        }
        $status->delete();
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

    public function LikeStatus(Request $request)
    {
        $status_id = $request['statusId'];
        $is_like = $request['isLike'] === 'true';
        $update = false;
        $status = Status::find($status_id);
        if (!$status) {
            return null;
        }
        $user = Auth::user();
        $like = $user->likes()->where('status_id', $status_id)->first();
        if ($like) {
            $already_like = $like->like;
            $update = true;
            if ($already_like == $is_like) {
                $like->delete();
                return null;
            }
        } else {
            $like = new Like();
        }
        $like->like = $is_like;
        $like->user_id = $user->id;
        $like->status_id = $status->id;
        if ($update) {
            $like->update();
        } else {
            $like->save();
        }
        return null;
    }

}
