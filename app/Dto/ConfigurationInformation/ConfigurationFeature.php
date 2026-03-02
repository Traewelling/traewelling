<?php

namespace App\Dto\ConfigurationInformation;

use App\Enum\ConfigurationFeatureEnum;
use App\Traits\JsonResponseObject;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'Feature',
    description: 'Represents a configuration feature and its status.',
    required: ['name', 'enabled'],
    properties: [
        new OA\Property(
            property: 'name',
            schema: '#/components/schemas/ConfigurationFeatureEnum',
            description: 'The name of the feature.',
            type: 'string',
            example: ConfigurationFeatureEnum::USER_REGISTRATION,
        ),
        new OA\Property(
            property: 'enabled',
            description: 'Indicates whether the feature is enabled.',
            type: 'boolean',
        ),
    ]
)]
readonly class ConfigurationFeature implements \JsonSerializable
{
    use JsonResponseObject;

    public function __construct(
        public ConfigurationFeatureEnum $name,
        public bool $enabled,
    ) {}
}
