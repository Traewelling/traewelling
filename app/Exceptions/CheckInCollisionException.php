<?php

namespace App\Exceptions;

use App\Models\Checkin;

class CheckInCollisionException extends Referencable
{
    public readonly Checkin $checkin;

    public function __construct(Checkin $checkin) {
        $this->checkin = $checkin;
        parent::__construct();
    }
}
