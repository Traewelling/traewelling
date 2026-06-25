<?php

namespace App\Repositories;

use App\Models\PrivacyPolicy;
use App\Models\PrivacyPolicyAcceptance;
use App\Models\User;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;

class PrivacyPolicyRepository
{
    public function getPrivacyPolicyValidAt(CarbonInterface $validAt): PrivacyPolicy
    {
        $cacheKey = 'privacy_policy.current.' . $validAt->format('Y-m-d-H');

        return Cache::remember($cacheKey, 300, fn () => PrivacyPolicy::where('valid_at', '<=', $validAt->toIso8601ZuluString())
            ->orderByDesc('valid_at')
            ->first());
    }

    public function getUpcomingPrivacyPolicy(): ?PrivacyPolicy
    {
        return PrivacyPolicy::where('valid_at', '>', now()->toIso8601ZuluString())
            ->orderBy('valid_at')
            ->first();
    }

    public function getPrivacyPolicyById(string $id): PrivacyPolicy
    {
        return PrivacyPolicy::whereId($id)->firstOrFail();
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
        return PrivacyPolicyAcceptance::leftJoin('privacy_policies', 'privacy_policies.id', '=', 'privacy_policy_acceptances.privacy_policy_id')
            ->where('user_id', $user->uuid)
            ->orderByDesc('privacy_policies.valid_at')
            ->first();
    }
}
