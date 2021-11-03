<?php

namespace App\Http\Controllers;

use App\Enum\StatusVisibility;
use App\Exceptions\AlreadyFollowingException;
use App\Exceptions\IdenticalModelException;
use App\Exceptions\PermissionException;
use App\Http\Controllers\Backend\User\SessionController;
use App\Http\Controllers\Backend\SettingsController as BackendSettingsController;
use App\Http\Controllers\Backend\User\TokenController;
use App\Models\Follow;
use App\Models\FollowRequest;
use App\Models\User;
use App\Notifications\FollowRequestApproved;
use App\Notifications\FollowRequestIssued;
use App\Notifications\UserFollowed;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Validator;
use Intervention\Image\ImageManagerStatic as Image;
use JetBrains\PhpStorm\ArrayShape;
use Mastodon;

/**
 * @deprecated Content will be moved to the backend/frontend/API packages soon, please don't add new functions here!
 */
class UserController extends Controller
{

    public static function getProfilePicture(User $user): array {
        $publicPath = public_path('/uploads/avatars/' . $user->avatar);

        if ($user->avatar == null || !file_exists($publicPath)) {
            return [
                'picture'   => self::generateDefaultAvatar($user),
                'extension' => 'png'
            ];
        }

        try {
            $ext     = pathinfo($publicPath, PATHINFO_EXTENSION);
            $picture = File::get($publicPath);
            return [
                'picture'   => $picture,
                'extension' => $ext
            ];
        } catch (Exception $e) {
            report($e);
            return [
                'picture'   => self::generateDefaultAvatar($user),
                'extension' => 'png'
            ];
        }
    }

    /**
     * @param User $user
     *
     * @return string Encoded PNG Image
     */
    private static function generateDefaultAvatar(User $user): string {
        $hash           = 0;
        $usernameLength = strlen($user->username);
        for ($i = 0; $i < $usernameLength; $i++) {
            $hash = ord(substr($user->username, $i, 1)) + (($hash << 5) - $hash);
        }

        $hex = dechex($hash & 0x00FFFFFF);

        return Image::canvas(512, 512, $hex)
                    ->insert(public_path('/img/user.png'))
                    ->encode('png')->getEncoded();
    }

    #[ArrayShape(['status' => "string"])]
    public static function updateProfilePicture($avatar): array {
        BackendSettingsController::updateProfilePicture($avatar);
        return ['status' => ':ok'];
    }

    /**
     * @todo remove twitterUrl after implemented ID-Link in vue Template
     */
    public static function getProfilePage($username): ?array {
        $user = User::where('username', 'like', $username)->first();
        if ($user === null) {
            return null;
        }
        try {
            $statuses = UserController::statusesForUser($user);
        } catch (PermissionException) {
            $statuses = null;
        }

        return [
            'username'    => $username,
            'statuses'    => $statuses,
            'twitterUrl'  => $user->twitterUrl,
            'mastodonUrl' => $user->mastodonUrl,
            'user'        => $user
        ];
    }

    /**
     * @param User $user
     *
     * @return LengthAwarePaginator|null
     * @throws PermissionException
     * @api v1
     * @frontend
     */
    public static function statusesForUser(User $user): ?LengthAwarePaginator {
        if ($user->userInvisibleToMe) {
            throw new PermissionException();
        }
        return $user->statuses()
                    ->join('train_checkins', 'statuses.id', '=', 'train_checkins.status_id')
                    ->with([
                               'user', 'likes', 'trainCheckin.Origin', 'trainCheckin.Destination',
                               'trainCheckin.HafasTrip.stopoversNEW', 'event'
                           ])
                    ->where(function($query) {
                        $user = Auth::check() ? auth()->user()->id : null;
                        $query->whereIn('statuses.visibility', [StatusVisibility::PUBLIC, StatusVisibility::UNLISTED])
                              ->orWhere('statuses.user_id', $user)
                              ->orWhere(function($query) {
                                  $followings = Auth::check() ? auth()->user()->follows()->select('follow_id') : [];
                                  $query->where('statuses.visibility', StatusVisibility::FOLLOWERS)
                                        ->whereIn('statuses.user_id', $followings);
                              });
                    })
                    ->select('statuses.*')
                    ->orderByDesc('train_checkins.departure')
                    ->paginate(15);
    }

    /**
     * @param User $user
     * @param User $userToFollow
     *
     * @return User
     * @throws AlreadyFollowingException
     * @throws IdenticalModelException
     * @api v1
     */
    public static function createOrRequestFollow(User $user, User $userToFollow): User {
        if ($user->is($userToFollow)) {
            throw new IdenticalModelException();
        }
        if ($user->follows->contains('id', $userToFollow->id) || $userToFollow->followRequests->contains('user_id', $user->id)) {
            throw new AlreadyFollowingException($user, $userToFollow);
        }

        // Request follow if user is a private profile
        if ($userToFollow->private_profile) {
            $follow = FollowRequest::create([
                                                'user_id'   => $user->id,
                                                'follow_id' => $userToFollow->id
                                            ]);

            $userToFollow->notify(new FollowRequestIssued($follow));
            $user->load('followRequests');
            $userToFollow->fresh();
            return $userToFollow; //FixMe somehow the refresh does not really work. The Request-Attribute is still false.
        }

        $follow = Follow::create([
                                     'user_id'   => $user->id,
                                     'follow_id' => $userToFollow->id
                                 ]);
        $user->notify(new FollowRequestApproved($follow));
        $userToFollow->fresh();
        return $userToFollow;
    }

    //Save Changes on Settings-Page

    /**
     * Add $userToFollow to $user's Followings
     *
     * @param User $user
     * @param User $userToFollow
     * @param bool $isApprovedRequest
     *
     * @return bool
     * @throws AlreadyFollowingException
     * @deprecated
     */
    public static function createFollow(User $user, User $userToFollow, bool $isApprovedRequest = false): bool {
        if ($user->is($userToFollow)) {
            return false;
        }

        //disallow re-following, if you already follow them
        //Also disallow following, if user is a private profile
        if (self::isFollowing($user, $userToFollow)) {
            throw new AlreadyFollowingException($user, $userToFollow);
        }
        // Request follow if user is a private profile
        if ($userToFollow->private_profile && !$isApprovedRequest) {
            return self::requestFollow($user, $userToFollow);
        }

        $follow = Follow::create([
                                     'user_id'   => $user->id,
                                     'follow_id' => $userToFollow->id
                                 ]);
        if (!$isApprovedRequest) {
            $userToFollow->notify(new UserFollowed($follow));
        } else {
            $user->notify(new FollowRequestApproved($follow));
        }
        $user->load('follows');
        return self::isFollowing($user, $userToFollow);
    }

    /**
     * Returnes whether $user follows $userFollow
     *
     * @param User $user
     * @param User $userFollow
     *
     * @return bool
     * @deprecated Following-Attribute
     */
    private static function isFollowing(User $user, User $userFollow): bool {
        return $user->follows->contains('id', $userFollow->id);
    }

    /**
     * Add $userToFollow to $user's FollowerRequests
     *
     * @param User $user
     * @param User $userToFollow The user of the person who is followed
     *
     * @return bool
     * @throws AlreadyFollowingException
     * @deprecated
     */
    public static function requestFollow(User $user, User $userToFollow): bool {
        if ($userToFollow->followRequests->contains('user_id', $user->id)) {
            throw new AlreadyFollowingException($user, $userToFollow);
        }
        $follow = FollowRequest::create([
                                            'user_id'   => $user->id,
                                            'follow_id' => $userToFollow->id
                                        ]);

        $userToFollow->notify(new FollowRequestIssued($follow));
        $userToFollow->load('followRequests');
        return $userToFollow->followRequests->contains('user_id', $user->id);
    }

    /**
     * Remove $userToUnfollow from $user's Follower
     *
     * @param User $user
     * @param User $userToUnfollow The user of the person who was followed and now isn't
     *
     * @return bool
     */
    public static function destroyFollow(User $user, User $userToUnfollow): bool {
        if (!self::isFollowing($user, $userToUnfollow)) {
            return false;
        }
        Follow::where('user_id', $user->id)->where('follow_id', $userToUnfollow->id)->delete();
        $user->load('follows');
        return self::isFollowing($user, $userToUnfollow) == false;
    }

    public static function registerByDay(Carbon $date): int {
        return User::where("created_at", ">=", $date->copy()->startOfDay())
                   ->where("created_at", "<=", $date->copy()->endOfDay())
                   ->count();
    }

    public static function updateDisplayName(string $displayName): bool {
        $request   = new Request(['displayName' => $displayName]);
        $validator = Validator::make($request->all(), [
            'displayName' => ['required', 'max:120']
        ]);
        if ($validator->fails()) {
            abort(400);
        }
        try {
            Auth::user()->update([
                                     'name' => $displayName
                                 ]);
            return true;
        } catch (Exception) {
            return false;
        }
    }

    /**
     * @param string|null $searchQuery
     * @deprecated is now in backend/usercontroller for api v1
     * @return Paginator
     */
    public static function searchUser(?string $searchQuery): Paginator {
        $validator = Validator::make(['searchQuery' => $searchQuery], ['searchQuery' => 'required|alpha_num']);
        if ($validator->fails()) {
            abort(400);
        }

        return User::where(
            'name', 'like', "%{$searchQuery}%"
        )->orWhere(
            'username', 'like', "%{$searchQuery}%"
        )->simplePaginate(10);
    }

    /**
     * @deprecated Backend/SettingsController::deleteProfilePicture in vue
     */
    public function deleteProfilePicture(): RedirectResponse {
        $user = Auth::user();

        if ($user->avatar != null) {
            File::delete(public_path('/uploads/avatars/' . $user->avatar));
            $user->update(['avatar' => null]);
        }

        return back();
    }

    public function deleteSession(): RedirectResponse {
        $user = Auth::user();
        Auth::logout();
        SessionController::deleteAllSessionsFor(user: $user);
        return redirect()->route('static.welcome');
    }

    /**
     * delete a specific session for user
     *
     * @param Request $request
     *
     * @return RedirectResponse
     */
    public function deleteToken(Request $request): RedirectResponse {
        $validated = $request->validate([
                                            'tokenId' => ['required', 'exists:oauth_access_tokens,id']
                                        ]);

        try {
            TokenController::revokeToken(tokenId: $validated['tokenId'], user: auth()->user());
            return redirect()->route('settings')->with('alert-success', __('settings.revoke-token.success'));
        } catch (PermissionException) {
            return redirect()->route('settings')->withErrors(__('messages.exception.general'));
        }
    }

    public function SaveAccount(Request $request): RedirectResponse {

        $this->validate($request, [
            'name' => 'required|max:120'
        ]);
        $user       = User::where('id', Auth::user()->id)->first();
        $user->name = $request['name'];
        $user->update();
        return redirect()->route('account');
    }
}
