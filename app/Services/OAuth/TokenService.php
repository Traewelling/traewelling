<?php

declare(strict_types=1);

namespace App\Services\OAuth;

use App\Models\User;
use App\Models\Webhook;
use App\Models\WebhookCreationRequest;
use Closure;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Laravel\Passport\AccessToken;
use Laravel\Passport\Passport;
use Laravel\Passport\Token;

class TokenService
{
    public function getActiveTokens(User $user): Collection
    {
        return $user->tokens()
            ->with(['client', 'refreshToken'])
            ->where($this->grantsAccess())
            ->get();
    }

    /**
     * The persisted token a user is currently authenticated with. Null when the request carries
     * no persisted token, which is the case for the first party session cookie: Passport builds
     * a transient token for it that has no database row and therefore nothing to revoke.
     */
    public function resolveCurrentToken(User $user): ?Token
    {
        $accessToken = $user->token();

        return match (true) {
            $accessToken instanceof Token => $accessToken,
            $accessToken instanceof AccessToken => Passport::token()->newQuery()
                ->find($accessToken->oauth_access_token_id),
            default => null,
        };
    }

    /**
     * Revokes an access token together with the refresh token that was issued with it. Revoking
     * the access token alone leaves the client able to obtain a new one until the refresh token
     * expires on its own.
     */
    public function revokeToken(Token $token): void
    {
        $token->revoke();

        Passport::refreshToken()->newQuery()
            ->where('access_token_id', $token->id)
            ->where('revoked', false)
            ->update(['revoked' => true]);
    }

    /**
     * Withdraws the access of the token's client: revokes the token pair and, once no token of
     * that client is left, deletes the webhooks the client registered for this user. Use this
     * where the user deliberately cuts off an application, not where a token is merely replaced.
     */
    public function revokeTokenAndClientWebhooks(Token $token): void
    {
        $this->revokeToken($token);

        $clientId = $token->client_id;
        if ($clientId === null) {
            return;
        }

        $hasRemainingToken = Token::query()
            ->where('client_id', $clientId)
            ->where('user_id', $token->user_id)
            ->where($this->grantsAccess())
            ->exists();

        if ($hasRemainingToken) {
            return;
        }

        $this->deleteWebhooks($token->user_id, $clientId);
    }

    /**
     * Revokes every access and refresh token of the user and drops all webhooks registered for
     * them, because no client is left that could be reached through one.
     */
    public function revokeAllTokens(User $user): void
    {
        Passport::refreshToken()->newQuery()
            ->where('revoked', false)
            ->whereIn('access_token_id', Token::query()->where('user_id', $user->id)->select('id'))
            ->update(['revoked' => true]);

        Token::query()
            ->where('user_id', $user->id)
            ->where('revoked', false)
            ->update(['revoked' => true]);

        $this->deleteWebhooks($user->id);
    }

    public function createPersonalAccessToken(User $user): string
    {
        $tokenResult = $user->createToken('PAT@' . $user->username, ['*']);
        $tokenResult->token->update(['expires_at' => now()->addMonths(3)]);

        return $tokenResult->accessToken;
    }

    private function grantsAccess(): Closure
    {
        return function (Builder $query): void {
            $query->where('revoked', false)
                ->where(function (Builder $lifetime): void {
                    $lifetime->where('expires_at', '>', now())
                        ->orWhereHas('refreshToken', function (Builder $refreshToken): void {
                            $refreshToken->where('revoked', false)
                                ->where('expires_at', '>', now());
                        });
                });
        };
    }

    private function deleteWebhooks(int $userId, ?int $clientId = null): void
    {
        Webhook::where('user_id', $userId)
            ->when($clientId !== null, fn (Builder $query) => $query->where('oauth_client_id', $clientId))
            ->delete();
        WebhookCreationRequest::where('user_id', $userId)
            ->when($clientId !== null, fn (Builder $query) => $query->where('oauth_client_id', $clientId))
            ->delete();
    }
}
