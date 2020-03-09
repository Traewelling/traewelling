<?php

namespace App\Http\Controllers;

use App\User;
use App\Follow;
use App\SocialLoginProfile;
use App\Status;
use App\HafasTrip;
use App\TrainCheckin;
use App\Like;
use App\Notifications\UserFollowed;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Jenssegers\Agent\Agent;
use Intervention\Image\ImageManagerStatic as Image;

class UserController extends Controller
{
    public static function getProfilePicture($username)
    {
        $user = User::where('username', $username)->first();
        if (empty($user)) {
            return null;
        }
        try {
            $ext     = pathinfo(public_path('/uploads/avatars/' . $user->avatar), PATHINFO_EXTENSION);
            $picture = File::get(public_path('/uploads/avatars/' . $user->avatar));
        } catch(\Exception $e) {
            $user->avatar = 'user.jpg';
        }

        if ($user->avatar === 'user.jpg') {
            $hash = 0;
            for ($i = 0; $i < strlen($username); $i++) {
                $hash = ord(substr($username, $i, 1)) + (($hash << 5) -$hash);
            }

            $hex = dechex($hash & 0x00FFFFFF);

            $picture = Image::canvas(512, 512, $hex)
                ->insert(public_path('/img/user.png'))
                ->encode('png')->getEncoded();
            $ext     = 'png';
        }

        return ['picture' => $picture, 'extension' => $ext];
    }

    public function updateSettings(Request $request)
    {
        $user = Auth::user();
        $this->validate($request, [
            'name' => ['required', 'string', 'max:50'],
            'avatar' => 'image'
        ]);
        if ($user->username != $request->username) {
            $this->validate($request, ['username' => ['required',
                                                      'string',
                                                      'max:25',
                                                      'regex:/^[a-zA-Z0-9_]*$/',
                                                      'unique:users']]);
        }
        if ($user->email != $request->email) {
            $this->validate($request, ['email' => ['required',
                                                   'string',
                                                   'email',
                                                   'max:255',
                                                   'unique:users']]);
            $user->email_verified_at = null;
        }

        $user->email      = $request->email;
        $user->username   = $request->username;
        $user->name       = $request->name;
        $user->always_dbl = $request->always_dbl == "on";
        $user->save();

        if (!$user->hasVerifiedEmail()) {
            $user->sendEmailVerificationNotification();
        }

        return $this->getAccount();
    }

    public function updatePassword(Request $request)
    {
        $user = Auth::user();
        if (Hash::check($request->currentpassword, $user->password) || empty($user->password)) {
            $this->validate($request, ['password' => ['required', 'string', 'min:8', 'confirmed']]);
            $user->password = Hash::make($request->password);
            $user->save();
            return redirect()->back()->with('info', __('controller.user.password-changed-ok'));
        }
        return redirect()->back()->withErrors(__('controller.user.password-wrong'));
    }

    public static function updateProfilePicture($avatar)
    {
        $user = Auth::user();

        $filename = $user->name . time() . '.png'; // Croppie always uploads a png
        Image::make($avatar)->resize(300, 300)->save( public_path('/uploads/avatars/' . $filename));

        if ($user->avatar != 'user.jpg') {
            File::delete(public_path('/uploads/avatars/' . $user->avatar));
        }
        $user->avatar = $filename;
        $user->save();

        return ['status' => ':ok'];
    }

    //Return Settings-page
    public function getAccount()
    {
        $user = Auth::user();
        $sessions = array();
        foreach($user->sessions as $session) {
            $sessionArray = array();
            $result       = new Agent();
            $result->setUserAgent($session->user_agent);
            $sessionArray['platform'] = $result->platform();

            if ($result->isphone()) {
                $sessionArray['device'] = 'mobile-alt';
            } elseif ($result->isTablet()) {
                $sessionArray['device'] = 'tablet';
            } else {
                $sessionArray['device'] = 'desktop';
            }
            $sessionArray['id']   = $session->id;
            $sessionArray['ip']   = $session->ip_address;
            $sessionArray['last'] = $session->last_activity;
            array_push($sessions, $sessionArray);
        }

        return view('settings', compact('user', 'sessions'));
    }

    //delete sessions from user
    public function deleteSession()
    {
        $user = Auth::user();
        foreach ($user->sessions as $session) {
            $session->delete();
        }
        return redirect()->route('static.welcome');
    }

    public function destroyUser()
    {
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
        DatabaseNotification::where(['notifiable_id' => $user->id, 'notifiable_type' => get_class($user)])->delete();

        $user->delete();

        return redirect()->route('static.welcome');
    }

    //Save Changes on Settings-Page
    public function SaveAccount(Request $request)
    {

        $this->validate($request, [
            'name' => 'required|max:120'
        ]);
        $user       = User::where('id', Auth::user()->id)->first();
        $user->name = $request['name'];
        $user->update();
        return redirect()->route('account');
    }

    public static function getProfilePage($username)
    {
        $user = User::where('username', $username)->first();
        if ($user === null) {
            return null;
        }
        $statuses = $user->statuses()->orderBy('created_at', 'DESC')->paginate(15);

        return ['username' => $username, 'statuses' => $statuses, 'user' => $user];

    }

    /**
     * @param User The user who wants to see stuff in their timeline
     * @param int The user id of the person who is followed
     */
    public static function CreateFollow($user, $followId)
    {
        $follow = $user->follows()->where('follow_id', $followId)->first();
        if ($follow) {
            return false;
        }
        $follow            = new Follow();
        $follow->user_id   = $user->id;
        $follow->follow_id = $followId;
        $follow->save();

        User::find($followId)->notify(new UserFollowed($follow));
        return true;
    }

    /**
     * @param User The user who doesn't want to see stuff in their timeline anymore
     * @param int The user id of the person who was followed and now isn't
     */
    public static function DestroyFollow($user, $followId)
    {
        $follow = $user->follows()->where('follow_id', $followId)->where('user_id', $user->id)->first();
        if ($follow) {
            $follow->delete();
            return true;
        }
    }

    public static function getLeaderboard()
    {
        $user    = Auth::user();
        $friends = null;

        if ($user != null) {
            $userIds   = $user->follows()->pluck('follow_id');
            $userIds[] = $user->id;
            $friends   = User::select('username',
                                      'train_duration',
                                      'train_distance',
                                      'points')
                ->where('points', '<>', 0)
                ->whereIn('id', $userIds)
                ->orderby('points', 'desc')
                ->limit(20)
                ->get();
        }
        $users      = User::select('username',
                                   'train_duration',
                                   'train_distance',
                                   'points')
            ->where('points', '<>', 0)
            ->orderby('points', 'desc')
            ->limit(20)
            ->get();
        $kilometers = User::select('username',
                                   'train_duration',
                                   'train_distance',
                                   'points')
            ->where('points', '<>', 0)
            ->orderby('train_distance', 'desc')
            ->limit(20)
            ->get();


        return ['users' => $users, 'friends' => $friends, 'kilometers' => $kilometers];
    }

    public static function registerByDay(Carbon $date)
    {
        $q = DB::table('users')
            ->select(DB::raw('count(*) as occurs'))
            ->where("created_at", ">=", $date->copy()->startOfDay())
            ->where("created_at", "<=", $date->copy()->endOfDay())
            ->first();
        return $q;
    }

    public static function updateDisplayName($displayname)
    {
        $request   = new Request(['displayname' => $displayname]);
        $validator = Validator::make($request->all(), [
            'displayname' => 'required|max:120'
        ]);
        if($validator->fails()){
            abort(400);
        }
        $user       = User::where('id', Auth::user()->id)->first();
        $user->name = $displayname;
        $user->save();
    }
}
