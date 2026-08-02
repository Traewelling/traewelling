<?php

declare(strict_types=1);

namespace App\Exceptions;

use Exception;

/**
 * Thrown when a trip cannot be deleted because checkins still reference it.
 */
class TripInUseException extends Exception {}
