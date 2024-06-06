<?php

namespace App\Exceptions;

use App\Models\PrivacyAgreement;
use App\Models\User;
use DateTime;

class AlreadyAcceptedException extends Referencable
{
    private readonly User             $user;
    private readonly PrivacyAgreement $privacyAgreement;

    /**
     * AlreadyFollowingException constructor.
     * $initiator is already following $user
     * OR
     * $initiator has already requested a follow to $user
     *
     * @param PrivacyAgreement $agreement privacyPolicy
     * @param User             $user
     */
    public function __construct(PrivacyAgreement $agreement, User $user) {
        $this->privacyAgreement = $agreement;
        $this->user             = $user;
        parent::__construct();
    }

    public function getPrivacyValidity(): DateTime {
        return $this->privacyAgreement->valid_at;
    }

    public function getUserAccepted(): DateTime {
        return $this->user->privacy_ack_at;
    }
}
