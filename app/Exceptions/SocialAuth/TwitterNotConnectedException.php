<?php 

namespace App\Exceptions\SocialAuth;

use Exception;

class TweetNotSendException extends Exception
{
    protected Status $status;
    protected int    $statusCode;

    /**
     * @param Status $status
     * @param int    $statusCOde
     */
    public function __construct(Status $status, int $statusCode) {
        parent::__construct();
        $this->status     = $status;
        $this->statusCode = $statusCode;
    }

    /**
     * @return Status
     */
    public function getStatus(): Status {
        return $this->status;
    }

    /**
     * @return int
     */
    public function getStatusCode(): int {
        return $this->statusCode;
    }
}
