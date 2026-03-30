<?php

namespace App\Repositories;

use App\Models\PrivacyPolicy;
use App\Models\PrivacyPolicyAcceptance;
use App\Models\User;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Collection;

class PrivacyPolicyRepository
{
    public function getPrivacyPolicyValidAt(CarbonInterface $validAt): PrivacyPolicy
    {
        return PrivacyPolicy::where('valid_at', '<=', $validAt->toIso8601ZuluString())
            ->orderByDesc('valid_at')
            ->first();
    }

    public function getUserPolicyAcceptance(User $user, ?PrivacyPolicy $privacyPolicy = null): Collection
    {
        $query = PrivacyPolicyAcceptance::where('user_id', $user->uuid);

        if ($privacyPolicy) {
            $query->where('privacy_policy_id', $privacyPolicy->id);
        }

        return $query->get();
    }

    public function acceptPrivacyPolicy(User $user, ?PrivacyPolicy $privacyPolicy = null): void
    {
        if (!$privacyPolicy) {
            $privacyPolicy = $this->getPrivacyPolicyValidAt(now());
        }

        PrivacyPolicyAcceptance::create([
            'privacy_policy_id' => $privacyPolicy->id,
            'user_id' => $user->uuid,
            'accepted_at' => now()->toIso8601ZuluString(),
        ]);
    }

    public function getLastAcceptedPolicy(User $user): ?PrivacyPolicyAcceptance
    {
        return PrivacyPolicyAcceptance::where('user_id', $user->uuid)
            ->orderByDesc('accepted_at')
            ->first();
    }
}
