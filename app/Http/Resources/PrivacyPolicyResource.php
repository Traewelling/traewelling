<?php

namespace App\Http\Resources;

use App\Models\PrivacyPolicy;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'Privacy Policy',
    required: ['validFrom', 'de', 'en', 'acceptedAt'],
    properties: [
        new OA\Property(
            property: 'validFrom',
            example: '2022-01-05T16:26:14.000000Z',
        ),
        new OA\Property(
            property: 'en',
            example: 'This is the english privacy policy',
        ),
        new OA\Property(
            property: 'de',
            example: 'Dies ist die deutsche Datenschutzerklärung',
        ),
        new OA\Property(
            property: 'acceptedAt',
            description: 'Has the current user already accepted this Privacy Policy?',
            example: '2022-01-05T16:26:14.000000Z',
            nullable: true,
        ),
    ],
    type: 'object'
)]
class PrivacyPolicyResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  Request  $request
     */
    public function toArray($request): array
    {
        /** @var PrivacyPolicy $this */
        return [
            'id' => $this->id,
            'validFrom' => $this->valid_at,
            'en' => $this->body_md_en,
            'de' => $this->body_md_de,
        ];
    }
}
