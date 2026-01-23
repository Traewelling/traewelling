<?php

namespace App\Dto\ConfigurationInformation;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'Language',
    type: 'object',
    properties: [
        new OA\Property(
            property: 'code',
            type: 'string',
            example: 'en',
            description: 'The language code (e.g., "en", "fr").'
        ),
        new OA\Property(
            property: 'name',
            type: 'string',
            example: 'English',
            description: 'The name of the language (e.g., "English", "French").'
        ),
    ],
    required: ['code', 'name'],
    description: 'Represents a language with its code and name.'
)]
readonly class Language
{
    use \App\Traits\JsonResponseObject;

    public function __construct(
        public string $code,
        public string $name,
    ) {}
}
