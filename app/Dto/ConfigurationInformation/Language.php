<?php

namespace App\Dto\ConfigurationInformation;

use App\Traits\JsonResponseObject;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'Language',
    description: 'Represents a language with its code and name.',
    required: ['code', 'name'],
    properties: [
        new OA\Property(
            property: 'code',
            description: 'The language code (e.g., "en", "fr").',
            type: 'string',
            example: 'en'
        ),
        new OA\Property(
            property: 'name',
            description: 'The name of the language (e.g., "English", "French").',
            type: 'string',
            example: 'English'
        ),
    ],
    type: 'object'
)]
readonly class Language
{
    use JsonResponseObject;

    public function __construct(
        public string $code,
        public string $name,
    ) {}
}
