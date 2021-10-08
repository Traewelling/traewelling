<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

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
}
