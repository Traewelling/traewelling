<?php

namespace App\Exceptions;

use App\Models\Status;
use App\Models\User;
use Exception;
use Throwable;

class StatusAlreadyLikedException extends Exception
{
    private $user;
    private $status;

    public function __construct(User $user, Status $status) {
        $this->user   = $user;
        $this->status = $status;
        parent::__construct();
    }

    public function getUser(): User {
        return $this->user;
    }

    public function getStatus(): Status {
        return $this->status;
    }
}
