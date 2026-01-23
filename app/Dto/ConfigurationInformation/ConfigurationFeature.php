<?php

namespace App\Dto\ConfigurationInformation;

use App\Enum\ConfigurationFeatureEnum;
use App\Traits\JsonResponseObject;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'Feature',
    properties: [
        new OA\Property(
            property: 'name',
            type: 'string',
            example: ConfigurationFeatureEnum::USER_REGISTRATION,
            enum: ConfigurationFeatureEnum::class,
            description: 'The name of the feature.',
        ),
        new OA\Property(
            property: 'enabled',
            type: 'boolean',
            description: 'Indicates whether the feature is enabled.',
        ),
    ],
    required: ['name', 'enabled'],
    description: 'Represents a configuration feature and its status.'
)]
readonly class ConfigurationFeature implements \JsonSerializable
{
    use JsonResponseObject;

    public function __construct(
        public ConfigurationFeatureEnum $name,
        public bool $enabled,
    ) {}
}
