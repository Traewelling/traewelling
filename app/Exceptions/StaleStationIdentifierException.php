<?php

declare(strict_types=1);

namespace App\Exceptions;

use Exception;

/**
 * The data provider reused a cached stop id for a different stop, so the id no longer belongs to the
 * station it is stored for.
 */
class StaleStationIdentifierException extends Exception {}
