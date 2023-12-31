<?php

namespace App\Exceptions;

use App\Models\Checkin;
use Exception;
use Throwable;

class CheckInCollisionException extends Referencable
{
    private Checkin $checkin;

    public function __construct(Checkin $checkin, $message = "", $code = 0, Throwable $previous = null) {
        $this->checkin = $checkin;
        parent::__construct($message, $code, $previous);
    }

    public function getCollision(): Checkin {
        return $this->checkin;
    }
}
