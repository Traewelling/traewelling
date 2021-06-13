<?php
declare(strict_types=0);

namespace App\Enum;

final class StatusVisibility extends BasicEnum
{
    public const PUBLIC    = 0;
    public const UNLISTED  = 1;
    public const FOLLOWERS = 2;
    public const PRIVATE   = 3;
}
