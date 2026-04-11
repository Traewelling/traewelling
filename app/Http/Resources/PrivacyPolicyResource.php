<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\PrivacyPolicy;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'Privacy Policy',
    required: ['id', 'validFrom', 'de', 'en', 'acceptedAt', 'hasOldAcceptance'],
    properties: [
        new OA\Property(
            property: 'id',
            description: 'UUID of the privacy policy',
            type: 'string',
            format: 'uuid',
            example: '00000000-0000-0000-0000-000000000000',
        ),
        new OA\Property(
            property: 'validFrom',
            description: 'Date and time from which this privacy policy is valid',
            type: 'string',
            format: 'date-time',
            example: '2022-01-05T16:26:14.000000Z',
        ),
        new OA\Property(
            property: 'en',
            description: 'Privacy policy text in English (Markdown)',
            type: 'string',
            example: 'This is the english privacy policy',
        ),
        new OA\Property(
            property: 'de',
            description: 'Privacy policy text in German (Markdown)',
            type: 'string',
            example: 'Dies ist die deutsche Datenschutzerklärung',
        ),
        new OA\Property(
            property: 'acceptedAt',
            description: 'When the current user accepted this privacy policy. Null if not yet accepted.',
            type: 'string',
            format: 'date-time',
            example: '2022-01-05T16:26:14.000000Z',
            nullable: true,
        ),
        new OA\Property(
            property: 'hasOldAcceptance',
            description: 'True if the user has accepted a previous (now outdated) version of the privacy policy.',
            type: 'boolean',
            example: false,
        ),
    ],
    type: 'object'
)]
class PrivacyPolicyResource extends JsonResource
{
    public function __construct(
        $resource,
        private readonly mixed $acceptedAt,
        private readonly bool $hasOldAcceptance,
    ) {
        parent::__construct($resource);
    }

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
            'acceptedAt' => $this->acceptedAt,
            'hasOldAcceptance' => $this->hasOldAcceptance,
        ];
    }
}
