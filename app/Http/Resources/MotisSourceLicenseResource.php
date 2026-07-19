<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\MotisSourceLicense;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Attributes as OA;

#[OA\Schema(
    title: 'MotisSourceLicense',
    description: 'A transit data source used by this instance, with its license information.',
    required: ['name', 'humanName', 'country', 'sourceUrl', 'spdx', 'licenseUrl', 'attributionText', 'active', 'forceActive', 'manualLicense'],
    properties: [
        new OA\Property(property: 'name', type: 'string', example: 'de-DELFI', nullable: true),
        new OA\Property(property: 'humanName', type: 'string', example: 'DELFI e.V.', nullable: true),
        new OA\Property(property: 'country', type: 'string', example: 'de', nullable: true),
        new OA\Property(property: 'sourceUrl', type: 'string', nullable: true),
        new OA\Property(property: 'spdx', type: 'string', example: 'CC-BY-4.0', nullable: true),
        new OA\Property(property: 'licenseUrl', type: 'string', nullable: true),
        new OA\Property(property: 'attributionText', type: 'string', nullable: true),
        new OA\Property(property: 'active', type: 'boolean'),
        new OA\Property(property: 'forceActive', type: 'boolean'),
        new OA\Property(
            property: 'manualLicense',
            properties: [
                new OA\Property(property: 'humanName', type: 'string', nullable: true),
                new OA\Property(property: 'licenseUrl', type: 'string', nullable: true),
            ],
            type: 'object',
            nullable: true,
        ),
    ],
)]
class MotisSourceLicenseResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray($request): array
    {
        /** @var MotisSourceLicense $this */
        return [
            'name' => $this->name,
            'humanName' => $this->human_name,
            'country' => $this->country,
            'sourceUrl' => $this->source_url,
            'spdx' => $this->spdx,
            'licenseUrl' => $this->license_url,
            'attributionText' => $this->attribution_text,
            'active' => (bool) $this->active,
            'forceActive' => (bool) $this->force_active,
            'manualLicense' => $this->manualLicense === null ? null : [
                'humanName' => $this->manualLicense->human_name,
                'licenseUrl' => $this->manualLicense->license_url,
            ],
        ];
    }
}
