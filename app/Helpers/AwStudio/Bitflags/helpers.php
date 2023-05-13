<?php
/**
 * Source from https://github.com/aw-studio/laravel-bitflags
 * Copyright aw-studio and contributors
 * https://github.com/aw-studio/laravel-bitflags/graphs/contributors
 */

if (!function_exists('addBitflag')) {
    function addBitflag(array|int $flag, array|int $bitmask) {
        $flag    = getBitmask($flag);
        $bitmask = getBitmask($bitmask);

        return $flag | $bitmask;
    }
}

if (!function_exists('removeBitflag')) {
    function removeBitflag(array|int $flag, array|int $bitmask) {
        $flag    = getBitmask($flag);
        $bitmask = getBitmask($bitmask);

        return $bitmask & ~$flag;
    }
}

if (!function_exists('getBitmask')) {
    function getBitmask(array|int $value) {
        if (is_int($value)) {
            return $value;
        }
        $bitmask = 0;
        foreach ($value as $bit) {
            if (!isPowerOfTwo($bit)) {
                throw new InvalidArgumentException($bit . ' is not a power of two.');
            }
            $bitmask = $bitmask | $bit;
        }

        return $bitmask;
    }
}

if (!function_exists('inBitmask')) {
    function inBitmask(int $flag, array|int $bitmask) {
        $bitmask = getBitmask($bitmask);

        return ($flag & $bitmask) == $flag;
    }
}

if (!function_exists('isPowerOfTwo')) {
    function isPowerOfTwo(int $number) {
        return ($number & ($number - 1)) == 0;
    }
}
