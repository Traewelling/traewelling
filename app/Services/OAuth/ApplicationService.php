<?php

declare(strict_types=1);

namespace App\Services\OAuth;

use App\Models\OAuthClient;
use App\Models\User;
use App\Repositories\OAuthClientRepository;
use App\Repositories\WebhookCallLogRepository;
use Illuminate\Support\Collection;
use Laravel\Passport\ClientRepository;

class ApplicationService
{
    public function __construct(
        private readonly OAuthClientRepository $oauthClientRepository,
        private readonly ClientRepository $clientRepository,
        private readonly WebhookCallLogRepository $webhookCallLogRepository,
    ) {}

    public function listForUser(User $user): Collection
    {
        return $this->clientRepository
            ->forUser($user)
            ->filter(fn ($c) => !$c->personal_access_client && !$c->password_client) // exclude personal access and password clients
            ->values();
    }

    public function findForUserOrAdmin(int $clientId, User $user): ?OAuthClient
    {
        if ($user->hasRole('admin')) {
            return $this->oauthClientRepository->find($clientId);
        }

        return $this->oauthClientRepository->findForUser($clientId, $user);
    }

    public function getWebhookStats(OAuthClient $client): array
    {
        $since = now()->subDays(7)->startOfDay();

        return [
            'client_id' => $client->id,
            'client_name' => $client->name,
            ...$this->webhookCallLogRepository->getStats($client->id, $since),
        ];
    }
}
