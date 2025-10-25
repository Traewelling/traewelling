<?php
declare(strict_types=1);

namespace App\Enum;

enum MotisCategory: string
{
    /**
     * Default: "TRANSIT"
     * Items Enum: "WALK" "BIKE" "RENTAL" "CAR" "CAR_PARKING" "CAR_DROPOFF" "ODM" "FLEX" "TRANSIT" "TRAM" "SUBWAY"
     * "FERRY" "AIRPLANE" "SUBURBAN" "BUS" "COACH" "RAIL" "HIGHSPEED_RAIL" "LONG_DISTANCE" "NIGHT_RAIL"
     * "REGIONAL_FAST_RAIL" "REGIONAL_RAIL" "CABLE_CAR" "FUNICULAR" "AERIAL_LIFT" "OTHER" "AREAL_LIFT" "METRO"
     */
    case WALK               = 'WALK';
    case BIKE               = 'BIKE';
    case RENTAL             = 'RENTAL';
    case CAR                = 'CAR';
    case CAR_PARKING        = 'CAR_PARKING';
    case CAR_DROPOFF        = 'CAR_DROPOFF';
    case ODM                = 'ODM';
    case FLEX               = 'FLEX';
    case TRANSIT            = 'TRANSIT';
    case TRAM               = 'TRAM';
    case SUBWAY             = 'SUBWAY';
    case FERRY              = 'FERRY';
    case AIRPLANE           = 'AIRPLANE';
    case SUBURBAN           = 'SUBURBAN';
    case BUS                = 'BUS';
    case COACH              = 'COACH';
    case RAIL               = 'RAIL';
    case HIGHSPEED_RAIL     = 'HIGHSPEED_RAIL';
    case LONG_DISTANCE      = 'LONG_DISTANCE';
    case NIGHT_RAIL         = 'NIGHT_RAIL';
    case REGIONAL_FAST_RAIL = 'REGIONAL_FAST_RAIL';
    case REGIONAL_RAIL      = 'REGIONAL_RAIL';
    case CABLE_CAR          = 'CABLE_CAR';
    case FUNICULAR          = 'FUNICULAR';
    case AERIAL_LIFT        = 'AERIAL_LIFT';
    case OTHER              = 'OTHER';
    case AERAL_LIFT         = 'AERAL_LIFT';
    case METRO              = 'METRO';


    // todo: this needs to be better
    public function getHTT(): HafasTravelType {
        return match ($this) {
            self::HIGHSPEED_RAIL, self::REGIONAL_FAST_RAIL                                    => HafasTravelType::NATIONAL_EXPRESS,
            self::LONG_DISTANCE                                                               => HafasTravelType::REGIONAL_EXP,
            self::METRO, self::SUBURBAN                                                       => HafasTravelType::SUBURBAN,
            self::BUS, self::COACH                                                            => HafasTravelType::BUS,
            self::FERRY                                                                       => HafasTravelType::FERRY,
            self::SUBWAY                                                                      => HafasTravelType::SUBWAY,
            self::TRAM, self::CABLE_CAR, self::FUNICULAR, self::AERIAL_LIFT, self::AERAL_LIFT => HafasTravelType::TRAM,
            default                                                                           => HafasTravelType::REGIONAL,
        };
    }

    // todo: this needs to be better
    public static function fromTravelType(TravelType|null $travelType): ?array {
        return match ($travelType) {
            TravelType::EXPRESS  => [MotisCategory::HIGHSPEED_RAIL, MotisCategory::LONG_DISTANCE, MotisCategory::NIGHT_RAIL, MotisCategory::REGIONAL_FAST_RAIL],
            TravelType::REGIONAL => [MotisCategory::REGIONAL_FAST_RAIL, MotisCategory::REGIONAL_RAIL],
            TravelType::SUBURBAN => [MotisCategory::METRO],
            TravelType::BUS      => [MotisCategory::BUS],
            TravelType::FERRY    => [MotisCategory::FERRY],
            TravelType::SUBWAY   => [MotisCategory::SUBWAY],
            TravelType::TRAM     => [MotisCategory::TRAM],
            TravelType::TAXI     => [MotisCategory::COACH],
            default              => null
        };
    }
}
