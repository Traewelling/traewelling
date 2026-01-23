<?php

namespace App\Dto\ConfigurationInformation;

use App\Traits\JsonResponseObject;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'ConfigurationInformation',
    type: 'object',
    properties: [
        new OA\Property(
            property: 'appName',
            type: 'string',
            example: 'Träwelling',
            description: 'The name of the application.'
        ),
        new OA\Property(
            property: 'appDebug',
            type: 'boolean',
            example: false,
            description: 'Indicates whether the application is in debug mode.'
        ),
        new OA\Property(
            property: 'appUrl',
            type: 'string',
            example: 'https://traewelling.de',
            description: 'The base URL of the application.'
        ),
        new OA\Property(
            property: 'features',
            type: 'array',
            description: 'A list of configuration features available in the application.',
            items: new OA\Items(ref: ConfigurationFeature::class)
        ),
        new OA\Property(
            property: 'languages',
            type: 'array',
            description: 'A list of supported languages in the application.',
            items: new OA\Items(ref: Language::class)
        ),
    ],
    required: ['appName', 'appDebug', 'appUrl', 'features', 'languages'],
    description: 'Holds configuration information about the application.'
)]
readonly class ConfigurationInformation implements \JsonSerializable
{
    use JsonResponseObject;

    public function __construct(
        public string $appName,
        public bool $appDebug,
        public string $appUrl,
        public array $features = [],
        public array $languages = [],
    ) {}
}
