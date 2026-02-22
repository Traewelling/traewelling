<?php

declare(strict_types=1);

namespace App\Enum;

use App\Interfaces\IconEnumInterface;

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

    public function faIcon(): ?string
    {
        return match ($this) {
            self::SEAT => 'fa-couch',
            self::ROLE => 'fa-briefcase',
            self::TICKET => 'fa-qrcode',
            self::PASSENGER_RIGHTS => 'fa-user-shield',
            self::JOURNEY_NUMBER => 'fa-hashtag',
            self::PRICE => 'fa-money-bill-wave',
            self::SOCIAL_STATUS => 'fa-comments',
            default => null,
        };
    }

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
