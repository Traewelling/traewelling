<?php
declare(strict_types=1);

namespace App\Enum;

enum MotisCategory: string
{
    /**
     * Default: "TRANSIT"
     * Items Enum: "WALK" "BIKE" "RENTAL" "CAR" "CAR_PARKING" "ODM" "TRANSIT" "TRAM" "SUBWAY" "FERRY" "AIRPLANE"
     * "METRO" "BUS" "COACH" "RAIL" "HIGHSPEED_RAIL" "LONG_DISTANCE" "NIGHT_RAIL" "REGIONAL_FAST_RAIL" "REGIONAL_RAIL"
     * "OTHER"
     */
    case TRANSIT            = 'TRANSIT';
    case WALK               = 'WALK';
    case BIKE               = 'BIKE';
    case RENTAL             = 'RENTAL';
    case CAR                = 'CAR';
    case CAR_PARKING        = 'CAR_PARKING';
    case ODM                = 'ODM';
    case TRAM               = 'TRAM';
    case SUBWAY             = 'SUBWAY';
    case FERRY              = 'FERRY';
    case AIRPLANE           = 'AIRPLANE';
    case METRO              = 'METRO';
    case BUS                = 'BUS';
    case COACH              = 'COACH';
    case RAIL               = 'RAIL';
    case HIGHSPEED_RAIL     = 'HIGHSPEED_RAIL';
    case LONG_DISTANCE      = 'LONG_DISTANCE';
    case NIGHT_RAIL         = 'NIGHT_RAIL';
    case REGIONAL_FAST_RAIL = 'REGIONAL_FAST_RAIL';
    case REGIONAL_RAIL      = 'REGIONAL_RAIL';
    case OTHER              = 'OTHER';


    // todo: this needs to be better
    public function getHTT(): HafasTravelType {
        return match ($this->name) {
            'HIGHSPEED_RAIL'     => HafasTravelType::NATIONAL_EXPRESS,
            'REGIONAL_FAST_RAIL' => HafasTravelType::NATIONAL,
            'METRO'              => HafasTravelType::SUBURBAN,
            'BUS'                => HafasTravelType::BUS,
            'FERRY'              => HafasTravelType::FERRY,
            'SUBWAY'             => HafasTravelType::SUBWAY,
            'TRAM'               => HafasTravelType::TRAM,
            'COACH'              => HafasTravelType::TAXI,
            default              => HafasTravelType::REGIONAL,
        };
    }

    // todo: this needs to be better
    public static function fromTravelType(TravelType|null $travelType): ?array {
        return match ($travelType) {
            TravelType::EXPRESS  => [MotisCategory::HIGHSPEED_RAIL,],
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
