<?php

namespace App\Exceptions;

use App\Models\TrainCheckin;
use Exception;
use Throwable;

class CheckInCollisionException extends Referencable
{
    private $trainCheckIn;

    public function __construct(TrainCheckin $trainCheckIn, $message = "", $code = 0, Throwable $previous = null) {
        $this->trainCheckIn = $trainCheckIn;
        parent::__construct($message, $code, $previous);
    }

    public function getCollision(): TrainCheckin {
        return $this->trainCheckIn;
    }
}
