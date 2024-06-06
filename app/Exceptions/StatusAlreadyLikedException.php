<?php

namespace App\Exceptions;

use App\Models\Status;
use App\Models\User;

class StatusAlreadyLikedException extends Referencable
{
    public readonly User   $user;
    public readonly Status $status;

    public function __construct(User $user, Status $status) {
        $this->user   = $user;
        $this->status = $status;
        parent::__construct();
    }
}
