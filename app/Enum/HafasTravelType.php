<?php
declare(strict_types=1);

namespace App\Enum;

enum HafasTravelType: string
{
    case NATIONAL_EXPRESS = 'nationalExpress';
    case NATIONAL         = 'national';
    case REGIONAL_EXP     = 'regionalExp';
    case REGIONAL         = 'regional';
    case SUBURBAN         = 'suburban';
    case BUS              = 'bus';
    case FERRY            = 'ferry';
    case SUBWAY           = 'subway';
    case TRAM             = 'tram';
    case TAXI             = 'taxi';

    public function getEmoji(): string {
        return match ($this->value) {
            'nationalExpress', 'national' => '🚄',
            'regionalExp', 'regional'     => '🚆',
            'suburban'                    => '🚋',
            'bus'                         => '🚌',
            'ferry'                       => '⛴',
            'subway'                      => '🚇',
            'tram'                        => '🚊',
            'taxi'                        => '🚖',
            default                       => '',
        };
    }

    public function onRails(): bool {
        return match ($this) {
            static::BUS, static::FERRY, static::TAXI => false,
            default                                  => true,
        };
    }
}
