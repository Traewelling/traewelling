<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\IcsToken;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Str;

class IcsTokenController extends Controller
{
    public static function createIcsToken(User $user, $name): IcsToken
    {
        return IcsToken::create([
            'user_id' => $user->id,
            'name' => $name,
            'token' => Str::uuid()->toString(),
        ]);
    }

    public static function revokeIcsToken(User $user, int $tokenId): void
    {
        $affectedRows = IcsToken::where('user_id', $user->id)
            ->where('id', $tokenId)
            ->delete();

        if ($affectedRows === 0) {
            throw new ModelNotFoundException();
        }
    }
}
