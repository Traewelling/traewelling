<?php

namespace App\Events;

use App\Models\Status;
use App\Models\User;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class UserCheckedIn
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    private User   $user;
    private Status $status;

    public function __construct(User $user, Status $status) {
        $this->user   = $user;
        $this->status = $status;
    }

    public function getUser(): User {
        return $this->user;
    }

    public function getStatus(): Status {
        return $this->status;
    }
}
