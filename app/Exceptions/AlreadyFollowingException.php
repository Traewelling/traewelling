<?php

namespace App\Exceptions;

use App\Models\User;

class AlreadyFollowingException extends Referencable
{
    public readonly User $user;
    public readonly User $initiator;

    /**
     * AlreadyFollowingException constructor.
     * $initiator is already following $user
     * OR
     * $initiator has already requested a follow to $user
     *
     * @param User $initiator
     * @param User $user
     */
    public function __construct(User $initiator, User $user) {
        $this->initiator = $initiator;
        $this->user      = $user;
        parent::__construct();
    }
}
