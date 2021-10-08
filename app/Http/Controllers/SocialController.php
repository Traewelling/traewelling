<?php

namespace App\Http\Controllers;

use App\Models\MastodonServer;
use App\Models\SocialLoginProfile;
use App\Models\User;
use File;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Mastodon;
use Redirect;
use Response;
use Socialite;
use Validator;

class SocialController extends Controller
{

    public function destroyProvider(Request $request) {
        $validated = $request->validate([
                                            'provider' => ['required', Rule::in(['twitter', 'mastodon'])]
                                        ]);

        $user = auth()->user();
        if ($user->password === null
            && !($user->socialProfile->twitter_id !== null && $user->socialProfile->mastodon_id !== null)) {
            return response(__('controller.social.delete-set-password'), 406);
        }
        if ($user->email === null
            && !($user->socialProfile->twitter_id !== null && $user->socialProfile->mastodon_id !== null)) {
            return response(__('controller.social.delete-set-email'), 406);
        }

        if ($user->socialProfile === null) {
            return response(__('controller.social.delete-never-connected'), 404);
        }

        if ($validated['provider'] == "twitter") {
            $user->socialProfile->update([
                                             'twitter_id'          => null,
                                             'twitter_token'       => null,
                                             'twitter_tokenSecret' => null
                                         ]);
        } elseif ($validated['provider'] == "mastodon") {
            $user->socialProfile->update([
                                             'mastodon_id'     => null,
                                             'mastodon_server' => null,
                                             'mastodon_token'  => null
                                         ]);
        }
        return response(__('controller.social.deleted'), 200);
    }

    /**
     * @deprecated Will be removed in future versions if it's not needed anymore
     */
    public function testMastodon(): void {
        $user           = Auth::user();
        $socialProfile  = $user->socialProfile;
        $mastodonDomain = MastodonServer::where('id', $socialProfile->mastodon_server)->first()->domain;

        Mastodon::domain($mastodonDomain)->token($socialProfile->mastodon_token);
        $response = Mastodon::createStatus('test1');
        dd($response);
    }
}
