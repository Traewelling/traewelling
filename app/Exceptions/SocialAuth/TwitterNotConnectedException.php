<?php 

namespace App\Exceptions\SocialAuth;

use Exception;

class TweetNotSendException extends Exception
{
    protected int    $statusCode;

    /**
     * @param Status $status
     * @param int    $statusCOde
     */
    public function __construct(int $statusCode) {
        parent::__construct();
        $this->statusCode = $statusCode;
    }

    /**
     * @return int
     */
    public function getStatusCode(): int {
        return $this->statusCode;
    }
}
