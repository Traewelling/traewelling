<?php
declare(strict_types=1);

namespace App\Enum;

final class HafasTravelType extends BasicEnum
{
    public const NATIONAL_EXPRESS = 'nationalExpress';
    public const NATIONAL         = 'national';
    public const REGIONAL_EXP     = 'regionalExp';
    public const REGIONAL         = 'regional';
    public const SUBURBAN         = 'suburban';
    public const BUS              = 'bus';
    public const FERRY            = 'ferry';
    public const SUBWAY           = 'subway';
    public const TRAM             = 'tram';
    public const TAXI             = 'taxi';
}
