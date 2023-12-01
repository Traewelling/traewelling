<?php

namespace App\Exceptions;

use App\Models\PrivacyAgreement;
use App\Models\User;
use Exception;

class AlreadyAcceptedException extends Referencable
{
    private User $user;

    /**
     * AlreadyFollowingException constructor.
     * $initiator is already following $user
     * OR
     * $initiator has already requested a follow to $user
     *
     * @param PrivacyAgreement privacyPolicy
     * @param User $user
     */
    public function __construct(PrivacyAgreement $agreement, User $user) {
        $this->privacyPolicy = $agreement;
        $this->user          = $user;
        parent::__construct();
    }

    public function getPrivacyValidity(): \DateTime {
        return $this->privacyPolicy->valid_at;
    }

    public function getUserAccepted(): \DateTime {
        return $this->user->privacy_ack_at;
    }
}
