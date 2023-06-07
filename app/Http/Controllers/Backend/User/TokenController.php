<?php

namespace App\Http\Controllers\Backend\User;

use App\Exceptions\PermissionException;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Webhook;
use App\Models\WebhookCreationRequest;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Laravel\Passport\Token;

abstract class TokenController extends Controller
{
    public static function index(User $user): object {
        return $user->tokens()
                    ->where('revoked', '=', '0')
                    ->where('expires_at', '>', now())
                    ->get();
    }

    /**
     * @throws PermissionException
     * @throws ModelNotFoundException
     */
    public static function revokeToken(string $tokenId, User $user): void {
        $token = Token::findOrFail($tokenId);

        if ($token->user->id !== $user->id) {
            throw new PermissionException();
        }

        $token->revoke();
        $client = $token->client()->first();
        if ($client) {
            $tokens = Token::where('client_id', $client->id)
                           ->where('user_id', $user->id)
                           ->where('revoked', '=', '0')
                           ->where('expires_at', '>', now())
                           ->count();
            if ($tokens < 1) {
                Webhook::where('oauth_client_id', $client->id)
                       ->where('user_id', $user->id)
                       ->delete();
                WebhookCreationRequest::where('oauth_client_id', $client->id)
                                      ->where('user_id', $user->id)
                                      ->delete();
            }
        }
    }

    public static function revokeAllTokens(User $user): void {
        Token::where('user_id', $user->id)->update(['revoked' => true]);
    }
}
