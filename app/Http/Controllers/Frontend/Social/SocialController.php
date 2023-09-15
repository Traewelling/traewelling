<?php

namespace App\Http\Controllers\Frontend\Social;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\Rule;

class SocialController extends Controller
{

    public function destroyProvider(Request $request): Response|Application|ResponseFactory {
        $validated = $request->validate([
                                            'provider' => ['required', Rule::in(['mastodon', 'twitter'])]
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

        if ($validated['provider'] === 'twitter') {
            //Twitter destroy is possible as we keep saving the last access tokens and account ids
            $user->socialProfile->update([
                                             'twitter_id'          => null,
                                             'twitter_token'       => null,
                                             'twitter_tokenSecret' => null
                                         ]);
        } elseif ($validated['provider'] === 'mastodon') {
            $user->socialProfile->update([
                                             'mastodon_id'     => null,
                                             'mastodon_server' => null,
                                             'mastodon_token'  => null
                                         ]);
        }
        return response(__('controller.social.deleted'), 200);
    }
}
