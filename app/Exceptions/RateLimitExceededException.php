<?php

namespace App\Exceptions;

class RateLimitExceededException extends Referencable
{
    /**
     * @var int|null RateLimit-Limit: containing the requests quota in the time window.
     */
    public readonly ?int $limit;

    /**
     * @var int|null RateLimit-Reset: containing the time remaining in the current window, specified in seconds.
     */
    public readonly ?int $reset;

    /**
     * @var int|null RateLimit-Remaining: containing the remaining requests quota in the current window.
     */
    public readonly ?int $remaining;

    public function __construct(?int $limit = null, ?int $reset = null, int $remaining = 0)
    {
        $this->limit = $limit;
        $this->reset = $reset;
        $this->remaining = $remaining;
        parent::__construct('Rate limit exceeded');
    }
}
