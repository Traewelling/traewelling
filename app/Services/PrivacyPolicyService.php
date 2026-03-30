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

    public function getPrivacyPolicy(?string $validAt = null): PrivacyPolicy
    {
        return $this->repository->getPrivacyPolicyValidAt(Carbon::parse($validAt));
    }

    public function getUserAcceptance(User $user): Collection
    {
        return $this->repository->getUserPolicyAcceptance($user);
    }

    /**
     * @throws AlreadyAcceptedException
     * @throws AcceptingOldPrivacyPolicyException
     */
    public function acceptPrivacyPolicy(User $user, ?string $validAt = null): void
    {
        $currentPolicy = $this->repository->getPrivacyPolicyValidAt(now());
        $privacyPolicy = $this->repository->getPrivacyPolicyValidAt(Carbon::parse($validAt));

        if ($privacyPolicy->id !== $currentPolicy->id && $privacyPolicy->valid_at->isBefore($currentPolicy->valid_at)) {
            throw new AcceptingOldPrivacyPolicyException(oldValidAt: $privacyPolicy->valid_at, currentValidAt: $currentPolicy->valid_at);
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
