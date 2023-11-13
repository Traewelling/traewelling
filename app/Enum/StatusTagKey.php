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

    public function faIcon(): ?string {
        return match ($this) {
            self::SEAT   => 'fa-couch',
            self::ROLE   => 'fa-briefcase',
            self::TICKET => 'fa-qrcode',
            default      => null,
        };
    }

    public function title(): ?string {
        $title = __('tag.title.' . $this->value);
        if (str_starts_with($title, 'tag.title.')) {
            return 'Tag: ' . $this->value;
        }
        return $title;
    }

    public function description(): ?string {
        return null;
    }
}
