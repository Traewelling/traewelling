<?php
declare(strict_types=1);

namespace App\Enum;

use App\OpenRailRoutingProfile;

/**
 * @OA\Schema(
 *     title="category",
 *     description="Category of transport. ",
 *     type="string",
 *     enum={"nationalExpress", "national", "regionalExp", "regional", "suburban", "bus", "ferry", "subway",
 *     "tram", "taxi", "plane"},
 *     example="suburban"
 * )
 */
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
    case PLANE            = 'plane';

    public function getEmoji(): string {
        return match ($this->value) {
            'nationalExpress'         => '🚄',
            'regionalExp', 'national' => '🚆',
            'regional'                => '🚞',
            'suburban'                => '🚋',
            'bus'                     => '🚌',
            'ferry'                   => '⛴',
            'subway'                  => '🚇',
            'tram'                    => '🚊',
            'taxi'                    => '🚖',
            'plane'                   => '✈️',
            default                   => '',
        };
    }

    public function onRails(): bool {
        return match ($this) {
            HafasTravelType::BUS, HafasTravelType::FERRY, HafasTravelType::TAXI => false,
            default                                                             => true,
        };
    }

    public function getORRProfile(): ?OpenRailRoutingProfile {
        return match ($this) {
            HafasTravelType::NATIONAL_EXPRESS, HafasTravelType::NATIONAL                        => OpenRailRoutingProfile::TGV_ALL,
            HafasTravelType::TRAM, HafasTravelType::SUBWAY                                      => OpenRailRoutingProfile::TRAM_TRAIN,
            HafasTravelType::REGIONAL_EXP, HafasTravelType::REGIONAL, HafasTravelType::SUBURBAN => OpenRailRoutingProfile::ALL_TRACKS,
            default                                                                             => null,
        };
    }
}
