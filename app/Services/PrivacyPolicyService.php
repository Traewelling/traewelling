<?php

namespace App\Services;

use App\Exceptions\AcceptingOldPrivacyPolicyException;
use App\Exceptions\AlreadyAcceptedException;
use App\Models\PrivacyPolicy;
use App\Models\PrivacyPolicyAcceptance;
use App\Models\User;
use App\Repositories\PrivacyPolicyRepository;
use Carbon\Carbon;
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
        $privacyPolicy = $this->repository->getPrivacyPolicyValidAt(Carbon::parse($validAt));

        if ($privacyPolicy->id !== $policy->id && $privacyPolicy->valid_at->isBefore($policy->valid_at)) {
            throw new AcceptingOldPrivacyPolicyException(oldValidAt: $privacyPolicy->valid_at, currentValidAt: $policy->valid_at);
        }

        $ack = $this->repository->getUserPolicyAcceptance($user, $privacyPolicy)->first();

        if ($ack) {
            throw new AlreadyAcceptedException(agreement: $privacyPolicy, ackAt: $ack->accepted_at);
        }

        $this->repository->acceptPrivacyPolicy($user, $privacyPolicy);
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
}
