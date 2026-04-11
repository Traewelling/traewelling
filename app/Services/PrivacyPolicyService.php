<?php

declare(strict_types=1);

namespace App\Services;

use App\Dto\PrivacyPolicyWithAcceptance;
use App\Exceptions\AcceptingOldPrivacyPolicyException;
use App\Exceptions\AlreadyAcceptedException;
use App\Models\PrivacyPolicy;
use App\Models\PrivacyPolicyAcceptance;
use App\Models\User;
use App\Repositories\PrivacyPolicyRepository;
use Illuminate\Database\Eloquent\Collection;

readonly class PrivacyPolicyService
{
    public function __construct(
        private PrivacyPolicyRepository $repository,
    ) {}

    public function getPrivacyPolicy(?string $id = null, ?User $visitingUser = null): ?PrivacyPolicy
    {
        if ($id === null) {
            return $this->repository->getPrivacyPolicyValidAt(now());
        }

        $policy = $this->repository->getPrivacyPolicyById($id);
        $currentPolicy = $this->repository->getPrivacyPolicyValidAt(now());

        if ($policy->id !== $currentPolicy->id && $policy->valid_at->isBefore($currentPolicy->valid_at) && !$visitingUser?->hasRole('admin')) {
            throw new AcceptingOldPrivacyPolicyException(oldValidAt: $policy->valid_at, currentValidAt: $currentPolicy->valid_at);
        }

        return $policy;
    }

    public function getUserAcceptance(User $user): Collection
    {
        return $this->repository->getUserPolicyAcceptance($user);
    }

    /**
     * @throws AlreadyAcceptedException
     * @throws AcceptingOldPrivacyPolicyException
     */
    public function acceptPrivacyPolicy(User $user, PrivacyPolicy $policy): void
    {
        $currentPolicy = $this->repository->getPrivacyPolicyValidAt(now());

        if ($currentPolicy->id !== $policy->id && $policy->valid_at->isBefore($currentPolicy->valid_at)) {
            throw new AcceptingOldPrivacyPolicyException(oldValidAt: $currentPolicy->valid_at, currentValidAt: $policy->valid_at);
        }

        $ack = $this->repository->getUserPolicyAcceptance($user, $policy)->first();

        if ($ack) {
            throw new AlreadyAcceptedException(agreement: $currentPolicy, ackAt: $ack->accepted_at);
        }

        $this->repository->acceptPrivacyPolicy($user, $policy);
    }

    public function hasUserAcceptedPolicy(User $user, ?PrivacyPolicy $policy = null): bool
    {
        if ($policy === null) {
            $policy = $this->repository->getPrivacyPolicyValidAt(now());
        }

        $ack = $this->repository->getUserPolicyAcceptance($user, $policy)->first();

        if ($ack) {
            return true;
        }

        return false;
    }

    public function getLastAcceptedPolicy(User $user): ?PrivacyPolicyAcceptance
    {
        return $this->repository->getLastAcceptedPolicy($user);
    }

    /**
     * Returns the current privacy policy together with the given user's acceptance status.
     */
    public function getPolicyWithAcceptanceStatus(?User $user): PrivacyPolicyWithAcceptance
    {
        $policy = $this->getPrivacyPolicy();
        $acceptedAt = null;
        $hasOldAcceptance = false;

        if ($user !== null) {
            $allAcceptances = $this->getUserAcceptance($user);
            $ownAcceptance = $allAcceptances->firstWhere('privacy_policy_id', $policy->id);
            $acceptedAt = $ownAcceptance?->accepted_at;
            $hasOldAcceptance = $acceptedAt === null && $allAcceptances->isNotEmpty();
        }

        return new PrivacyPolicyWithAcceptance($policy, $acceptedAt, $hasOldAcceptance);
    }
}
