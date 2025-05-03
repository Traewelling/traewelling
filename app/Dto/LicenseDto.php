<?php

declare(strict_types=1);

namespace App\Dto;

class LicenseDto
{
    public string  $licenseName;
    public ?string $attributionString;
    public ?string $licenseUrl;
    public ?string $sourceUrl;

    public function __construct(
        string  $licenseName,
        ?string $attributionString = null,
        ?string $licenseUrl = null,
        ?string $sourceUrl = null
    ) {
        $this->licenseName       = $licenseName;
        $this->attributionString = $attributionString;
        $this->licenseUrl        = $licenseUrl;
        $this->sourceUrl         = $sourceUrl;
    }
}
