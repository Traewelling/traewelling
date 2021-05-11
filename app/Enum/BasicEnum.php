<?php
declare(strict_types=1);

namespace App\Enum;

use ReflectionClass;

/**
 * Delete after Enum will be added in php8.1
 */
abstract class BasicEnum
{
    private static ?array $constCacheArray = null;

    public static function getList(): array {
        return self::getConstants();
    }

    private static function getConstants(): array {
        if (self::$constCacheArray === null) {
            self::$constCacheArray = [];
        }

        $calledClass = get_called_class();
        if (!array_key_exists($calledClass, self::$constCacheArray)) {
            $reflect                             = new ReflectionClass($calledClass);
            self::$constCacheArray[$calledClass] = $reflect->getConstants();
        }
        return self::$constCacheArray[$calledClass];
    }

    public static function isValidName($name, bool $strict = false): bool {
        $constants = self::getConstants();

        if ($strict) {
            return array_key_exists($name, $constants);
        }

        $keys = array_map('strtolower', array_keys($constants));
        return in_array(strtolower($name), $keys);
    }

    public static function isValidValue($value, $strict = true): bool {
        $values = array_values(self::getConstants());
        return in_array($value, $values, $strict);
    }
}
