<?php

namespace App\Exceptions;

use Exception;

class MissingParametersExection extends Exception
{
    public function __construct(string $parameterName) {
        $message = "Missing Parameter: " . $parameterName;
        parent::__construct($message);
    }
}
