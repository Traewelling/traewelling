<?php

namespace App\Http\Controllers;

use App\User;
use App\Follow;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;


class UserController extends Controller
{
    public function updateSettings(Request $request) {
        $user = Auth::user();

        $user->username = $request->username;
        $user->name = $request->name;
        $user->save();
        return view('settings', compact('user'));
    }

    //Return Settings-page
    public function getAccount() {
        return view('settings', ['user' => Auth::user()]);
    }

    //Save Changes on Settings-Page
    public function SaveAccount(Request $request) {

        $this->validate($request, [
            'name' => 'required|max:120'
        ]);
        $user = Auth::user();
        $user->name = $request['name'];
        $user->update();
        $file = $request->file('image');
        $filename = $request['name'].'-'.$user->id.'.jpg';

        if ($file) {
            Storage::disk('local')->put($filename, File::get($file));
        }
        return redirect()->route('account');
    }

    public function getUserImage($filename){
        $file = Storage::disk('local')->get($filename);
        return new Response($file, 200);
    }

    public function getProfilePage($username) {
        $user = User::where('username', $username)->first();
        $statuses = $user->statuses()->get();
        return view('profile', ['username' => $username, 'statuses' => $statuses, 'user' => $user]);
    }

    #ToDo
    public function getFollows(){

    }

    public function CreateFollow(Request $request) {
        $follow_id = $request['follow_id'];
        $user = Auth::user();
        $follow = $user->follows()->where('follow_id', $follow_id)->first();
        if ($follow) {
            return response()->json(['message' => 'This follow already exists.'], 409);
        } else {
            $follow = new Follow();
        }
        $follow->user_id = $user->id;
        $follow->follow_id = $follow_id;
        $follow->save();
        return response()->json(['message' => 'Followed user.'], 201);
    }

    public function DestroyFollow(Request $request) {
        $follow_id = $request['follow_id'];
        $user = Auth::user();
        $follow = $user->follows()->where('follow_id', $follow_id)->first();
        if ($follow) {
            if (Auth::user() != $follow->user) {
                return response()->json(['message' => 'This action is not permitted.'], 403);
            }
            $follow->delete();
            return response()->json(['message' => 'This follow has been destroyed.'], 200);
        }
        return response()->json(['message' => 'This follow does not exist.'], 409);
    }

}
