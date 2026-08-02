<?php

declare(strict_types=1);

namespace App\Exceptions;

use Exception;

/**
 * Thrown when a stopover cannot be removed because a checkin still references it
 * as its origin or destination.
 */
class StopoverInUseException extends Exception {}
