<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Rules\SecureUrl;
use Illuminate\Foundation\Http\FormRequest;
use OpenApi\Attributes as OA;

#[OA\Schema(
    title: 'StoreOAuthClientRequest',
    required: ['name', 'redirect'],
    properties: [
        new OA\Property(property: 'name', type: 'string', example: 'My App'),
        new OA\Property(property: 'redirect', type: 'string', example: 'https://example.com/callback'),
        new OA\Property(property: 'confidential', type: 'boolean', example: true),
        new OA\Property(property: 'webhooksEnabled', type: 'boolean', example: false),
        new OA\Property(property: 'authorizedWebhookUrl', type: 'string', example: 'https://example.com/webhook', nullable: true),
        new OA\Property(property: 'privacyPolicyUrl', type: 'string', example: 'https://example.com/privacy', nullable: true),
    ],
    type: 'object'
)]
class StoreOAuthClientRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'redirect' => ['required', 'string', 'url', new SecureUrl()],
            'confidential' => ['boolean'],
            'webhooksEnabled' => ['boolean'],
            'authorizedWebhookUrl' => ['nullable', 'url', new SecureUrl()],
            'privacyPolicyUrl' => ['nullable', 'url', new SecureUrl()],
        ];
    }
}
