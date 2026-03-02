<?php

namespace App\Http\Resources;

use App\Models\OAuthClient;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Attributes as OA;

#[OA\Schema(
    title: 'Client',
    required: ['id', 'name', 'privacyPolicyUrl'],
    properties: [
        new OA\Property(property: 'id', type: 'integer', example: 1),
        new OA\Property(property: 'name', type: 'string', example: 'Träwelling App'),
        new OA\Property(
            property: 'privacyPolicyUrl',
            type: 'string',
            example: 'https://traewelling.de/privacy-policy',
        ),
    ],
)]
class ClientResource extends JsonResource
{
    public function toArray($request)
    {
        /** @var OAuthClient $this */
        return [
            'id' => $this->id,
            'name' => $this->name,
            'privacyPolicyUrl' => $this->privacy_policy_url,
        ];
    }
}
