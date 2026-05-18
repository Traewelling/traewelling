<?php

declare(strict_types=1);

namespace App\Dto;

use App\Models\PrivacyPolicy;
use Illuminate\Support\Carbon;

readonly class PrivacyPolicyWithAcceptance
{
    public function __construct(
        public PrivacyPolicy $policy,
        public ?Carbon $acceptedAt,
        public bool $hasOldAcceptance,
        public ?PrivacyPolicy $upcomingPolicy = null,
        public ?Carbon $upcomingAcceptedAt = null,
    ) {}
}
