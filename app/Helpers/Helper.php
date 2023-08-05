<?php

use Illuminate\Support\Carbon;

/**
 * @see https://stackoverflow.com/a/437642
 */
function number($number, $decimals = 2) {
    return number_format($number, $decimals,
                         __('dates.decimal_point'),
                         __('dates.thousands_sep'));
}

/**
 * Calculate hours and minutes from a given duration in seconds.
 *
 * @param int $seconds How long in seconds?
 *
 * @return array with `hours`, `minutes` and `showHours`.
 */
function secondsToDuration($seconds): array {
    $secondsInAnHour = 60 * 60;

    return [
        "hours"     => intdiv($seconds, $secondsInAnHour),
        "minutes"   => intdiv($seconds % $secondsInAnHour, 60),
        "showHours" => $seconds >= $secondsInAnHour
    ];
}

/**
 * @param array $duration from the secondsToDuration
 *
 * @return string
 */
function durationToSpan($duration): string {
    $return = $duration["minutes"] . "<small>min</small>";

    if ($duration["showHours"]) {
        $return = $duration["hours"] . "<small>h</small>&nbsp;" . $return;
    }

    return $return;
}

function userTime(null|Carbon|\Carbon\Carbon|string $time = null, ?string $format = null, bool $iso = true): string {
    if ($time === null) {
        return '';
    }
    $format   = $format ?? __('time-format');
    $time     = $time instanceof \Carbon\Carbon ? $time : Carbon::parse($time);
    $timezone = auth()->user()->timezone ?? config('app.timezone');
    if ($iso) {
        return $time->tz($timezone)->isoFormat($format);
    }
    return $time->tz($timezone)->format($format);
}
