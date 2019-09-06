<?php

namespace App\Http\Controllers;

use App\User;
use App\Follow;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Jenssegers\Agent\Agent;


class UserController extends Controller
{
    public function updateSettings(Request $request) {
        $user = Auth::user();

        $user->email = $request->email;
        $user->username = $request->username;
        $user->name = $request->name;
        $user->save();
        return $this->getAccount();
    }

    //Return Settings-page
    public function getAccount() {
        $user = Auth::user();
        $sessions = array();
        foreach($user->sessions as $session) {
            $session_array = array();
            $result = new Agent();
            $result->setUserAgent($session->user_agent);
            $session_array['platform'] = $result->platform();

            if ($result->isphone()) {
                $session_array['device'] = 'mobile-alt';
            } elseif ( $result->isTablet()) {
                $session_array['device'] = 'tablet';
            } else {
                $session_array['device'] = 'desktop';
            }
            $session_array['id'] = $session->id;
            $session_array['ip'] = $session->ip_address;
            $session_array['last'] = $session->last_activity;
            array_push($sessions, $session_array);
        }

        return view('settings', compact('user', 'sessions'));
    }

    //delete sessions from user
    public function deleteSession(Request $request) {
        $user = Auth::user();
        foreach ($user->sessions as $session) {
            $session->delete();
        }
        return redirect()->route('welcome');
    }

    //Save Changes on Settings-Page
    public function SaveAccount(Request $request) {

        $this->validate($request, [
            'name' => 'required|max:120'
        ]);
        $user = User::where('id', Auth::user()->id)->first();
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
        $statuses = $user->statuses()->orderBy('created_at', 'DESC')->paginate(15);
        return view('profile', ['username' => $username, 'statuses' => $statuses, 'user' => $user]);
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

    public function getLeaderboard(Request $request) {
        $user = Auth::user();
        $friends = null;

        if ($user != null) {
            $userIds = $user->follows()->pluck('follow_id');
            $userIds[] = $user->id;
            $friends = User::select('username', 'train_duration', 'train_distance', 'points')->whereIn('id', $userIds)->orderby('points', 'desc')->limit(20)->get();
        }
        $users = User::select('username', 'train_duration', 'train_distance', 'points')->orderby('points', 'desc')->limit(20)->get();
        $kilometers = User::select('username', 'train_duration', 'train_distance', 'points')->orderby('train_distance', 'desc')->limit(20)->get();



        return view('leaderboard', compact('users', 'friends', 'kilometers'));
    }

}
