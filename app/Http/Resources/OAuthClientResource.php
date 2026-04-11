<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Attributes as OA;

#[OA\Schema(
    title: 'OAuthClientResource',
    description: 'OAuth application owned by the authenticated user',
    required: ['id', 'name', 'redirect', 'confidential', 'webhooksEnabled', 'activeTokensCount', 'hasWebhooks', 'createdAt'],
    properties: [
        new OA\Property(property: 'id', type: 'integer', example: 42),
        new OA\Property(property: 'name', type: 'string', example: 'My App'),
        new OA\Property(property: 'redirect', type: 'string', example: 'https://example.com/callback'),
        new OA\Property(property: 'confidential', type: 'boolean', example: true),
        new OA\Property(property: 'webhooksEnabled', type: 'boolean', example: false),
        new OA\Property(property: 'authorizedWebhookUrl', type: 'string', example: 'https://example.com/webhook', nullable: true),
        new OA\Property(property: 'privacyPolicyUrl', type: 'string', example: 'https://example.com/privacy', nullable: true),
        new OA\Property(property: 'activeTokensCount', type: 'integer', example: 3),
        new OA\Property(property: 'hasWebhooks', type: 'boolean', example: true),
        new OA\Property(property: 'plainSecret', description: 'Only present immediately after creation or secret regeneration', type: 'string', example: 'abc123', nullable: true),
        new OA\Property(property: 'createdAt', type: 'string', format: 'date-time', example: '2026-01-01T00:00:00Z'),
    ],
    type: 'object'
)]
class OAuthClientResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'redirect' => $this->redirect,
            'confidential' => $this->isConfidential(),
            'webhooksEnabled' => (bool) $this->webhooks_enabled,
            'authorizedWebhookUrl' => $this->authorized_webhook_url,
            'privacyPolicyUrl' => $this->privacy_policy_url,
            'activeTokensCount' => $this->active_tokens_count ?? $this->tokens()->where('revoked', false)->count(),
            'hasWebhooks' => $this->has_webhooks ?? $this->webhooks()->exists(),
            'plainSecret' => $this->plain_secret,
            'createdAt' => $this->created_at->toIso8601String(),
        ];
    }
}
