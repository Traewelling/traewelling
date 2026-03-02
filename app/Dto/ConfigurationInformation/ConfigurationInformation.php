<?php

namespace App\Dto\ConfigurationInformation;

use App\Traits\JsonResponseObject;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'ConfigurationInformation',
    description: 'Holds configuration information about the application.',
    required: ['appName', 'appDebug', 'appUrl', 'features', 'languages', 'version'],
    properties: [
        new OA\Property(
            property: 'appName',
            description: 'The name of the application.',
            type: 'string',
            example: 'Träwelling'
        ),
        new OA\Property(
            property: 'appDebug',
            description: 'Indicates whether the application is in debug mode.',
            type: 'boolean',
            example: false
        ),
        new OA\Property(
            property: 'appUrl',
            description: 'The base URL of the application.',
            type: 'string',
            example: 'https://traewelling.de'
        ),
        new OA\Property(
            property: 'version',
            description: 'The current version of the application.',
            type: 'string',
            example: '1.0.0'
        ),
        new OA\Property(
            property: 'features',
            description: 'A list of configuration features available in the application.',
            type: 'array',
            items: new OA\Items(ref: ConfigurationFeature::class)
        ),
        new OA\Property(
            property: 'languages',
            description: 'A list of supported languages in the application.',
            type: 'array',
            items: new OA\Items(ref: Language::class)
        ),
    ],
    type: 'object'
)]
readonly class ConfigurationInformation implements \JsonSerializable
{
    use JsonResponseObject;

    public function __construct(
        public string $appName,
        public bool $appDebug,
        public string $appUrl,
        public string $version,
        public array $features = [],
        public array $languages = [],
    ) {}
}
