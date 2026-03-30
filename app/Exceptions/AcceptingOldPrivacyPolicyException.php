<?php

namespace App\Exceptions;

use Carbon\Carbon;
use Exception;

class AcceptingOldPrivacyPolicyException extends Exception
{
    public function __construct(
        public readonly Carbon $oldValidAt,
        public readonly Carbon $currentValidAt,
    ) {}
}
