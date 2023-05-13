<?php

namespace App\Helpers\AwStudio\Bitflags\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use InvalidArgumentException;

/**
 * Source from https://github.com/aw-studio/laravel-bitflags
 * Copyright aw-studio and contributors
 * https://github.com/aw-studio/laravel-bitflags/graphs/contributors
 */
class Bitflags implements CastsAttributes
{
    /**
     * Cast the given value.
     *
     * @param \Illuminate\Database\Eloquent\Model $model
     * @param string                              $key
     * @param mixed                               $value
     * @param array                               $attributes
     *
     * @return array
     */
    public function get($model, $key, $value, $attributes) {
        $bits = array_reverse(str_split(decbin($value)));
        $mask = [];
        foreach ($bits as $i => $bit) {
            if ($bit != '0') {
                $mask[] = 1 << $i;
            }
        }
        return $mask;
    }

    /**
     * Prepare the given value for storage.
     *
     * @param \Illuminate\Database\Eloquent\Model $model
     * @param string                              $key
     * @param array                               $value
     * @param array                               $attributes
     *
     * @return string
     */
    public function set($model, $key, $value, $attributes) {
        if (is_array($value)) {
            $bitmask = 0;
            foreach ($value as $bit) {
                if (!$this->isPowerOfTwo($bit)) {
                    throw new InvalidArgumentException($bit . ' is not a power of two.');
                }
                $bitmask = $bitmask | $bit;
            }

            return $bitmask;
        }

        return $value;
    }

    public function isPowerOfTwo(int $number) {
        return ($number & ($number - 1)) == 0;
    }
}
