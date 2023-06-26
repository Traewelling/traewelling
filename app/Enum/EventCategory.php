<?php declare(strict_types=1);

namespace App\Enum;

enum EventCategory: int
{
    case PUBLIC_HOLIDAY         = 1;
    case TRANSPORTATION         = 2;
    case POLITICAL              = 3;
    case EXHIBITION             = 4;
    case PRIDE                  = 5;
    case LOCAL                  = 6;
    case SPORT_COMPETITION      = 7;
    case MUSIC_CONCERT_FESTIVAL = 8;
    case HACKATHON_CODING       = 9;
}
