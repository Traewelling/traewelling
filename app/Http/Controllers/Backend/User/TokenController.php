<?php

namespace App\Http\Controllers\Backend\User;

use App\Exceptions\PermissionException;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Laravel\Passport\Token;

class TokenController extends Controller
{
    public static function index(User $user): object {
        return $user->tokens->where('revoked', '0');
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
    }

    public static function revokeAllTokens(User $user): void {
        Token::where('user_id', $user->id)->update(['revoked' => true]);
    }
}
