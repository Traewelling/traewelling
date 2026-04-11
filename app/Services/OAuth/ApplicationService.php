<?php

declare(strict_types=1);

namespace App\Services\OAuth;

use App\Models\OAuthClient;
use App\Models\User;
use App\Repositories\OAuthClientRepository;
use App\Repositories\WebhookCallLogRepository;
use Illuminate\Support\Collection;
use Laravel\Passport\ClientRepository;

readonly class ApplicationService
{
    public function __construct(
        private OAuthClientRepository $oauthClientRepository,
        private ClientRepository $clientRepository,
        private WebhookCallLogRepository $webhookCallLogRepository,
    ) {}

    public function listForUser(User $user): Collection
    {
        return OAuthClient::query()
            ->where('user_id', $user->id)
            ->where('personal_access_client', false)
            ->where('password_client', false)
            ->where('revoked', false)
            ->withCount(['tokens as active_tokens_count' => fn ($q) => $q->where('revoked', false)])
            ->withExists('webhooks as has_webhooks')
            ->get();
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
