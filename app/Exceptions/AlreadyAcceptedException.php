<?php

namespace App\Exceptions;

use App\Models\PrivacyPolicy;
use Carbon\CarbonInterface;
use DateTime;

class AlreadyAcceptedException extends Referencable
{
    private readonly PrivacyPolicy $privacyAgreement;

    private readonly CarbonInterface $ackAt;

    /**
     * AlreadyFollowingException constructor.
     * $initiator is already following $user
     * OR
     * $initiator has already requested a follow to $user
     *
     * @param  PrivacyPolicy  $agreement  privacyPolicy
     */
    public function __construct(PrivacyPolicy $agreement, CarbonInterface $ackAt)
    {
        $this->privacyAgreement = $agreement;
        $this->ackAt = $ackAt;
        parent::__construct();
    }

    public function getPrivacyValidity(): DateTime
    {
        return $this->privacyAgreement->valid_at;
    }

    public function getUserAccepted(): CarbonInterface
    {
        return $this->ackAt;
    }
}
