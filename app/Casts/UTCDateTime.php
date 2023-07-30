<?php

namespace App\Casts;

use Carbon\Exceptions\InvalidTimeZoneException;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * This cast is very crucial for handling and storing dates supplied in other timezones than the app's specified one
 * Since Laravel's MySQL query builder will just strip the timezone identifier down to Y-m-d H:i:s, we need to be sure,
 * that the query builder will always receive a datetime model converted to UTC (or the application's default timezone)
 *
 * On that note: Please don't change the timezone.
 * You'll be better off just changing your mySQL server's timezone to UTC
 */
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
     * @throws InvalidTimeZoneException
     */
    public function set($model, string $key, $value, array $attributes): Carbon|null {
        if ($value === null) {
            return null;
        }
        if (!$value instanceof Carbon) {
            if (is_string($value) && !str_contains($value, '+')) {
                throw new InvalidTimeZoneException("Given timestamp has no valid timezone in it -> $value");
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
