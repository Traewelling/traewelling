<?php
declare(strict_types=1);

namespace App\Enum;

use App\Interfaces\IconEnumInterface;

/**
 * @todo Rename this to something better matching the context.
 *       -> TravelPurpose?
 *
 * @OA\Schema(
 *     title="Business",
 *     description="What type of travel (0=private, 1=business, 2=commute) did the user specify?",
 *     type="integer",
 *     enum={0,1,2},
 *     example=0,
 * )
 */
enum Business: int implements IconEnumInterface
{
    case PRIVATE  = 0;
    case BUSINESS = 1;
    case COMMUTE  = 2;

    public function faIcon(): string {
        return match ($this) {
            self::PRIVATE  => 'fa-user',
            self::BUSINESS => 'fa-briefcase',
            self::COMMUTE  => 'fa-building'
        };
    }

    public function title(): string {
        return match ($this) {
            self::PRIVATE  => __('stationboard.business.private'),
            self::BUSINESS => __('stationboard.business.business'),
            self::COMMUTE  => __('stationboard.business.commute')
        };
    }

    public function description(): string {
        return match ($this) {
            self::PRIVATE  => '',
            self::BUSINESS => __('stationboard.business.business.detail'),
            self::COMMUTE  => __('stationboard.business.commute.detail')
        };
    }
}
