<?php

namespace App\Http\Controllers;

use App\User;
use App\Follow;
use App\SocialLoginProfile;
use App\Status;
use App\HafasTrip;
use App\TrainCheckin;
use App\Like;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Hash;
use Jenssegers\Agent\Agent;
use Image;

class UserController extends Controller
{
    public function updateSettings(Request $request) {
        $user = Auth::user();
        $this->validate($request, [
            'name' => ['required', 'string', 'max:50'],
            'avatar' => 'image'
        ]);
        if ($user->username != $request->username) {
            $this->validate($request,['username' => ['required', 'string', 'max:25', 'regex:/^[a-zA-Z0-9_]*$/', 'unique:users']]);
        }
        if ($user->email != $request->email) {
            $this->validate($request, ['email' => ['required', 'string', 'email', 'max:255', 'unique:users']]);
            $user->email_verified_at = null;
        }
        if ($request->hasFile('avatar')) {
            $avatar = $request->file('avatar');
            $filename = $user->name . time() . '.' . $avatar->getClientOriginalExtension();
            Image::make($avatar)->resize(300, 300)->save( public_path('/uploads/avatars/' . $filename));

            if ($user->avatar != 'user.jpg') {
                File::delete(public_path('/uploads/avatars/' . $user->avatar));
            }
            $user->avatar = $filename;
        }

        $user->email = $request->email;
        $user->username = $request->username;
        $user->name = $request->name;
        $user->save();

        if (!$user->hasVerifiedEmail()) {
            $user->sendEmailVerificationNotification();
        }

        return $this->getAccount();
    }

    public function updatePassword(Request $request) {
        $user = Auth::user();
        if (Hash::check($request->currentpassword, $user->password)) {
            $this->validate($request, ['password' => ['required', 'string', 'min:8', 'confirmed']]);
            $user->password = Hash::make($request->password);
            $user->save();
            return redirect()->back()->with('info', __('controller.user.password-changed-ok'));
        }
        return redirect()->back()->withErrors(__('controller.user.password-wrong'));
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

    public function destroyUser(Request $request) {
        $user = Auth::user();
        
        if ($user->avatar != 'user.jpg') {
            File::delete(public_path('/uploads/avatars/' . $user->avatar));
        }
        foreach(Status::where('user_id', $user->id)->get() as $status) {
            TrainCheckin::where('status_id', $status->id)->delete();
            $status->likes()->delete();
            $status->delete();
        }

        SocialLoginProfile::where('user_id', $user->id)->delete();
        Follow::where('user_id', $user->id)->orWhere('follow_id', $user->id)->delete();

        $user->delete();

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
            return response()->json(['message' => __('controller.user.follow-already-exists')], 409);
        } else {
            $follow = new Follow();
        }
        $follow->user_id = $user->id;
        $follow->follow_id = $follow_id;
        $follow->save();
        return response()->json(['message' => __('controller.user.follow-ok')], 201);
    }

    public function DestroyFollow(Request $request) {
        $follow_id = $request['follow_id'];
        $user = Auth::user();
        $follow = $user->follows()->where('follow_id', $follow_id)->first();
        if ($follow) {
            if (Auth::user() != $follow->user) {
                return response()->json(['message' => __('controller.user.follow-delete-not-permitted')], 403);
            }
            $follow->delete();
            return response()->json(['message' => __('controller.user.follow-destroyed')], 200);
        }
        return response()->json(['message' => __('controller.user.follow-404')], 409);
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
