<?php

namespace App\Http\Controllers;

use Abraham\TwitterOAuth\TwitterOAuth;
use App\Exceptions\AlreadyFollowingException;
use App\Models\Follow;
use App\Models\FollowRequest;
use App\Models\MastodonServer;
use App\Models\Status;
use App\Models\TrainCheckin;
use App\Models\User;
use App\Notifications\FollowRequestApproved;
use App\Notifications\FollowRequestIssued;
use App\Notifications\UserFollowed;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Validator;
use Intervention\Image\ImageManagerStatic as Image;
use JetBrains\PhpStorm\ArrayShape;
use Laravel\Passport\Token;
use Mastodon;

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

    public function deleteProfilePicture(): RedirectResponse {
        $user = Auth::user();

        if ($user->avatar != null) {
            File::delete(public_path('/uploads/avatars/' . $user->avatar));
            $user->update(['avatar' => null]);
        }

        return back();
    }

    #[ArrayShape(['status' => "string"])]
    public static function updateProfilePicture($avatar): array {
        $filename = strtr(':userId_:time.png', [ // Croppie always uploads a png
                                                 ':userId' => Auth::user()->id,
                                                 ':time'   => time()
        ]);
        Image::make($avatar)->resize(300, 300)
             ->save(public_path('/uploads/avatars/' . $filename));

        if (Auth::user()->avatar != null) {
            File::delete(public_path('/uploads/avatars/' . Auth::user()->avatar));
        }

        Auth::user()->update([
                                 'avatar' => $filename
                             ]);

        return ['status' => ':ok'];
    }


    public function deleteSession(): RedirectResponse {
        $user = Auth::user();
        Auth::logout();
        foreach ($user->sessions as $session) {
            $session->delete();
        }
        return redirect()->route('static.welcome');
    }

    /**
     * delete a specific session for user
     * @param Request $request
     * @return RedirectResponse
     */
    public function deleteToken(Request $request): RedirectResponse {
        $validated = $request->validate([
                                            'tokenId' => ['required', 'exists:oauth_access_tokens,id']
                                        ]);
        $token     = Token::find($validated['tokenId']);
        if ($token->user->id == Auth::user()->id) {
            $token->revoke();
        }
        return redirect()->route('settings');
    }

    //Save Changes on Settings-Page
    public function SaveAccount(Request $request): RedirectResponse {

        $this->validate($request, [
            'name' => 'required|max:120'
        ]);
        $user       = User::where('id', Auth::user()->id)->first();
        $user->name = $request['name'];
        $user->update();
        return redirect()->route('account');
    }

    public static function getProfilePage($username): ?array {
        $user = User::where('username', 'like', $username)->first();
        if ($user === null) {
            return null;
        }
        $statuses = null;

        if (!$user->userInvisibleToMe) {
            $statuses = $user->statuses()->with('user',
                                                'trainCheckin',
                                                'trainCheckin.Origin',
                                                'trainCheckin.Destination',
                                                'trainCheckin.HafasTrip',
                                                'event')->orderBy('created_at', 'DESC')->paginate(15);
        }
        $user->unsetRelation('socialProfile');

        return [
            'username'    => $username,
            'twitterUrl'  => $user->twitterUrl,
            'mastodonUrl' => $user->mastodonUrl,
            'statuses'    => $statuses,
            'user'        => $user
        ];
    }

    /**
     * Add $userToFollow to $user's Followings
     * @param User $user The user who initiated the follow
     * @param User $userToFollow The user who is followed
     * @param bool $isApprovedRequest Differentiates between who to be notified
     * @return bool
     * @throws AlreadyFollowingException
     */
    public static function createFollow(User $user, User $userToFollow, bool $isApprovedRequest = false): bool {
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
     * Add $userToFollow to $user's FollowerRequests
     * @param User $user
     * @param User $userToFollow The user of the person who is followed
     * @return bool
     * @throws AlreadyFollowingException
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
     * @param User $user
     * @param User $userToUnfollow The user of the person who was followed and now isn't
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

    /**
     * Returnes whether $user follows $userFollow
     * @param User $user
     * @param User $userFollow
     * @return bool
     */
    private static function isFollowing(User $user, User $userFollow): bool {
        return $user->follows->contains('id', $userFollow->id);
    }

    #[ArrayShape([
        'users'      => "Illuminate\\Support\\Collection",
        'friends'    => "Illuminate\\Support\\Collection",
        'kilometers' => "Illuminate\\Support\\Collection"
    ])]
    public static function getLeaderboard(): array {
        $checkIns = TrainCheckin::with(['status.user', 'HafasTrip.stopoversNEW.trainStation', 'Origin', 'Destination'])
                                ->where('departure', '>=', Carbon::now()->subDays(7)->toIso8601String())
                                ->get();

        $trainCheckIns = (clone $checkIns)
            ->groupBy('status.user_id')
            ->map(function($trainCheckIns) {
                return [
                    'user'     => $trainCheckIns->first()->status->user,
                    'points'   => $trainCheckIns->sum('points'),
                    'distance' => $trainCheckIns->sum('distance'),
                    'duration' => $trainCheckIns->sum('duration'),
                    'speed'    => $trainCheckIns->avg('speed')
                ];
            });

        $friendsTrainCheckIns = null;
        if (Auth::check()) {
            $friendsTrainCheckIns = (clone $checkIns)
                ->filter(function($trainCheckIn) {
                    return Auth::user()->follows
                            ->pluck('id')
                            ->contains($trainCheckIn->status->user_id)
                        || $trainCheckIn->status->user_id == Auth::user()->id;
                })
                ->groupBy('status.user_id')
                ->map(function($trainCheckIns) {
                    return [
                        'user'     => $trainCheckIns->first()->status->user,
                        'points'   => $trainCheckIns->sum('points'),
                        'distance' => $trainCheckIns->sum('distance'),
                        'duration' => $trainCheckIns->sum('duration'),
                        'speed'    => $trainCheckIns->avg('speed')
                    ];
                });
        }

        return [
            'users'      => (clone $trainCheckIns)->sortByDesc('points'),
            'friends'    => $friendsTrainCheckIns?->sortByDesc('points') ?? null,
            'kilometers' => (clone $trainCheckIns)->sortByDesc('distance')
        ];
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

    public static function getMonthlyLeaderboard(Carbon $date): Collection {
        return Status::with(['trainCheckin', 'user'])
                     ->join('train_checkins', 'train_checkins.status_id', '=', 'statuses.id')
                     ->where(
                         'train_checkins.departure',
                         '>=',
                         $date->clone()->firstOfMonth()->toDateString()
                     )
                     ->where(
                         'train_checkins.departure',
                         '<=',
                         $date->clone()->lastOfMonth()->toDateString() . ' 23:59:59'
                     )
                     ->get()
                     ->groupBy('user_id')
                     ->map(function($statuses) {
                         return [
                             'user'        => $statuses->first()->user,
                             'points'      => $statuses->sum('trainCheckin.points'),
                             'distance'    => $statuses->sum('distance'),
                             'duration'    => $statuses->sum('trainCheckin.duration'),
                             'statusCount' => $statuses->count()
                         ];
                     })
                     ->sortByDesc('points');
    }
}
