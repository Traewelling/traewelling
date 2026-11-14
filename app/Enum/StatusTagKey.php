<?php

declare(strict_types=1);

namespace App\Enum;

use App\Interfaces\IconEnumInterface;
use OpenApi\Attributes as OA;

#[OA\Schema(
    title: 'StatusTagKey',
    description: 'StatusTagKey',
    type: 'string',
    example: 'trwl:social_status',
    enum: [
        'trwl:seat',
        'trwl:wagon',
        'trwl:ticket',
        'trwl:travel_class',
        'trwl:locomotive_class',
        'trwl:wagon_class',
        'trwl:role',
        'trwl:vehicle_number',
        'trwl:passenger_rights',
        'trwl:journey_number',
        'trwl:price',
        'trwl:social_status',
    ],
)]
enum StatusTagKey: string implements IconEnumInterface
{
    case SEAT = 'trwl:seat';
    case WAGON = 'trwl:wagon';
    case TICKET = 'trwl:ticket';
    case TRAVEL_CLASS = 'trwl:travel_class';
    case LOCOMOTIVE_CLASS = 'trwl:locomotive_class';
    case WAGON_CLASS = 'trwl:wagon_class';
    case ROLE = 'trwl:role';
    case VEHICLE_NUMBER = 'trwl:vehicle_number';
    case PASSENGER_RIGHTS = 'trwl:passenger_rights';
    case JOURNEY_NUMBER = 'trwl:journey_number';
    case PRICE = 'trwl:price';
    case SOCIAL_STATUS = 'trwl:social_status';

    public function title(): ?string
    {
        $title = __('tag.title.' . $this->value);
        if (str_starts_with($title, 'tag.title.')) {
            return $this->value;
        }

        return $title;
    }

    public function description(): ?string
    {
        return null;
    }

    /**
     * Returns the list of allowed values for this tag key, or null if the value is free-text.
     *
     * @return string[]|null
     */
    public function allowedValues(): ?array
    {
        return match ($this) {
            self::SOCIAL_STATUS => ['open', 'open_find_me', 'open_lets_hang', 'do_not_disturb'],
            default => null,
        };
    }
}
