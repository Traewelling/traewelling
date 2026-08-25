<?php

declare(strict_types=1);

namespace App\Enum;

use OpenApi\Attributes as OA;

#[OA\Schema(
    title: 'TagSuggestionSource',
    description: 'Describes why a tag was suggested.',
    type: 'string',
    example: 'trip',
    enum: ['trip', 'recent', 'frequent'],
)]
enum TagSuggestionSource: string
{
    /** Another user already tagged the trip you are about to check into. */
    case TRIP = 'trip';

    /** The user recently used this tag. */
    case RECENT = 'recent';

    /** The user frequently used this tag in the last days. */
    case FREQUENT = 'frequent';
}
