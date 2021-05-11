<?php
declare(strict_types=1);

namespace Tests\Enum;

use App\Enum\BasicEnum;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use TypeError;

class BasicEnumTest extends TestCase
{
    public function testGetList() {
        $this->assertEmpty(BasicEnum::getList());
        $this->assertEquals(
            (new ReflectionClass(MockEnumClass::class))->getConstants(),
            MockEnumClass::getList()
        );
    }

    public function testIsValidName() {
        $this->assertTrue(MockEnumClass::isValidName('TEST_CONST'));
        $this->assertFalse(MockEnumClass::isValidName('FALSE_TEST_CONST'));

        $this->expectException(TypeError::class);
        MockEnumClass::isValidName(2);

    }

    public function testIsValidValue() {
        $this->assertTrue(MockEnumClass::isValidValue(null));
        $this->assertTrue(MockEnumClass::isValidValue(1));
        $this->assertFalse(MockEnumClass::isValidValue(2));
    }
}
