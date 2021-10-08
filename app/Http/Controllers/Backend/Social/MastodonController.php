<?php

namespace App\Http\Controllers\Backend\Social;

use App\Http\Controllers\Controller;
use App\Models\MastodonServer;
use App\Models\SocialLoginProfile;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

abstract class MastodonController extends Controller
{
    /**
     * Function to create a user with a login-provider.
     * If logged in, the user will have the login-provider added.
     * If a user with corresponding login-provider already exists, it will be returned.
     *
     * @param $getInfo (response of Socialite->user())
     * @param $domain
     *
     * @return User|RedirectResponse|null model
     */
    public static function createUser($getInfo, $domain): User|RedirectResponse|null {
        $identifier = SocialLoginProfile::where('mastodon_id', $getInfo->id)
                                        ->where(
                                            'mastodon_server',
                                            MastodonServer::where('domain', $domain)->first()->id
                                        )
                                        ->first();

        if (Auth::check()) {
            $user = Auth::user();
            if ($identifier != null) {
                return redirect()->to('/dashboard')->withErrors([__('controller.social.already-connected-error')]);
            }
        } elseif ($identifier === null) {
            $existingUser = User::where('username', $getInfo->nickname)->first();
            $errorCount   = 0;
            while ($errorCount < 10 && $existingUser !== null) {
                $getInfo->nickname = $getInfo->nickname . rand(1, 10);
                $existingUser      = User::where('username', $getInfo->nickname)->first();
                $errorCount++;
            }
            try {
                $user = User::create([
                                         'name'     => $getInfo->name,
                                         'username' => $getInfo->nickname,
                                         'email'    => $getInfo->email,
                                     ]);
            } catch (QueryException) {
                return null;
            }
        } else {
            $user = User::where('id', $identifier->user_id)->first();
        }

        $socialProfile                  = $user->socialProfile ?: new SocialLoginProfile;
        $socialProfile->mastodon_id     = $getInfo->id;
        $socialProfile->mastodon_token  = $getInfo->token;
        $socialProfile->mastodon_server = MastodonServer::where('domain', $domain)->first()->id;

        $user->socialProfile()->save($socialProfile);
        return $user;
    }
}
