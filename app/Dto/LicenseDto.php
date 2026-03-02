<?php

declare(strict_types=1);

namespace App\Dto;

use OpenApi\Attributes as OA;

#[OA\Schema(
    title: 'License DTO',
    description: 'Data Transfer Object for licenses',
    required: ['licenseName', 'attributionString', 'licenseUrl', 'sourceUrl'],
)]
class LicenseDto
{
    #[OA\Property(property: 'licenseName', description: 'Name of the license', example: 'CC BY 4.0')]
    public string $licenseName;

    #[OA\Property(
        property: 'attributionString',
        description: 'Attribution string for the license',
        example: 'Provided by OpenStreetMap contributors',
        nullable: true,
    )]
    public ?string $attributionString;

    #[OA\Property(
        property: 'licenseUrl',
        description: 'URL to the license text',
        example: 'https://creativecommons.org/licenses/by/4.0/',
        nullable: true,
    )]
    public ?string $licenseUrl;

    #[OA\Property(
        property: 'sourceUrl',
        description: 'URL to the source of the data',
        example: 'https://www.openstreetmap.org/',
        nullable: true,
    )]
    public ?string $sourceUrl;

    public function __construct(
        string $licenseName,
        ?string $attributionString = null,
        ?string $licenseUrl = null,
        ?string $sourceUrl = null
    ) {
        $this->licenseName = $licenseName;
        $this->attributionString = $attributionString;
        $this->licenseUrl = $licenseUrl;
        $this->sourceUrl = $sourceUrl;
    }
}
