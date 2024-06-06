<?php

namespace App\Policies;

use App\Models\User;
use Laravel\Passport\Token;

class TokenPolicy
{

    public function delete(User $user, Token $token): bool {
        return $token->user->id === $user->id;
    }

}
