<?php

namespace App\Exceptions;

use Illuminate\Support\Collection;

class CheckInCollisionException extends Referencable
{
    public readonly Collection $checkins;

    public function __construct(Collection $checkins)
    {
        $this->checkins = $checkins;
        parent::__construct();
    }
}
