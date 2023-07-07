<?php

namespace App\Casts;

use Exception;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class UTCDateTime implements CastsAttributes
{
    /**
     *
     * @param Model  $model
     * @param string $key
     * @param mixed  $value
     * @param array  $attributes
     *
     * @return Carbon|null
     * @throws Exception
     */
    public function set($model, string $key, $value, array $attributes): Carbon|null
    {
        if ($value === null) {
            return null;
        }
        if (!$value instanceof Carbon) {
            if (is_string($value) && !str_contains($value, '+')) {
                throw new Exception("THIS IS A WONKY LOCAL TIME!!1 -> $value");
            }
            $value = Carbon::parse($value);
        }
        return $value->tz(config('app.timezone'));
    }

    public function get($model, string $key, $value, array $attributes) {
        if ($value === null) {
            return null;
        }
        if (!$value instanceof Carbon) {
            $value = Carbon::parse($value, config('app.timezone'));
        }
        return $value;
    }
}
