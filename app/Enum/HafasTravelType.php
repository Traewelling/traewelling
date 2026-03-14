<?php

declare(strict_types=1);

namespace App\Enum;

use OpenApi\Attributes as OA;

#[OA\Schema(
    title: 'category',
    description: 'Category of transport.',
    type: 'string',
    example: 'suburban',
    enum: ['nationalExpress', 'national', 'regionalExp', 'regional', 'suburban', 'bus', 'ferry', 'subway', 'tram', 'taxi', 'plane', 'freightTrain'],
)]
enum HafasTravelType: string
{
    case NATIONAL_EXPRESS = 'nationalExpress';
    case NATIONAL = 'national';
    case REGIONAL_EXP = 'regionalExp';
    case REGIONAL = 'regional';
    case SUBURBAN = 'suburban';
    case BUS = 'bus';
    case FERRY = 'ferry';
    case SUBWAY = 'subway';
    case TRAM = 'tram';
    case TAXI = 'taxi';
    case PLANE = 'plane';
    case FREIGHT_TRAIN = 'freightTrain';

    public function getEmoji(): string
    {
        return match ($this->value) {
            'nationalExpress' => '🚄',
            'regionalExp', 'national' => '🚆',
            'regional' => '🚞',
            'suburban' => '🚋',
            'bus' => '🚌',
            'ferry' => '⛴',
            'subway' => '🚇',
            'tram' => '🚊',
            'taxi' => '🚖',
            'plane' => '✈️',
            'freightTrain' => '🚂',
            default => '',
        };
    }

    public function onRails(): bool
    {
        return match ($this) {
            HafasTravelType::BUS, HafasTravelType::FERRY, HafasTravelType::TAXI => false,
            default => true,
        };
    }

    public function getORRProfile(): ?OpenRailRoutingProfile
    {
        return match ($this) {
            HafasTravelType::NATIONAL_EXPRESS, HafasTravelType::NATIONAL => OpenRailRoutingProfile::TGV_ALL,
            HafasTravelType::TRAM, HafasTravelType::SUBWAY => OpenRailRoutingProfile::TRAM_TRAIN,
            HafasTravelType::REGIONAL_EXP, HafasTravelType::REGIONAL, HafasTravelType::SUBURBAN, HafasTravelType::FREIGHT_TRAIN => OpenRailRoutingProfile::ALL_TRACKS,
            default => null,
        };
    }
}
