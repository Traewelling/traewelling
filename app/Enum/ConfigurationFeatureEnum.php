<?php

namespace App\Enum;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'ConfigurationFeatureEnum',
    type: 'string',
    enum: [
        ConfigurationFeatureEnum::USER_REGISTRATION,
        ConfigurationFeatureEnum::YEAR_IN_REVIEW,
    ],
    description: 'Enumeration of configuration features available in the application.'
)]
enum ConfigurationFeatureEnum: string
{
    case USER_REGISTRATION = 'user_registration';
    case YEAR_IN_REVIEW = 'year_in_review';
}
