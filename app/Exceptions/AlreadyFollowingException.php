<?php

namespace App\Exceptions;

use App\Models\User;
use Exception;

class AlreadyFollowingException extends Referencable
{
    private User $user;
    private User $initiator;

    /**
     * AlreadyFollowingException constructor.
     * $initiator is already following $user
     * OR
     * $initiator has already requested a follow to $user
     * @param User $initiator
     * @param User $user
     */
    public function __construct(User $initiator, User $user) {
        $this->initiator = $initiator;
        $this->user      = $user;
        parent::__construct();
    }

    public function getUser(): User {
        return $this->user;
    }

    public function getInitiator(): User {
        return $this->initiator;
    }
}
