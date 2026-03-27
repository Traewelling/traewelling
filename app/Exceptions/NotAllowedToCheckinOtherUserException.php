<?php

namespace App\Exceptions;

use Exception;

class NotAllowedToCheckinOtherUserException extends Exception
{
    public function __construct(
        public array $users,
    ) {
        parent::__construct();
    }
}
