<?php
declare(strict_types=1);

namespace Tests\Enum;

use App\Enum\BasicEnum;

class MockEnumClass extends BasicEnum
{
    public const TEST_CONST         = 1;
    public const TEST_OTHER_CONST   = 'test';
    public const TEST_ANOTHER_CONST = null;
}
