<?php

use App\Exceptions\Referencable;
use App\Models\User;
use Carbon\CarbonTimeZone;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

/**
 * BEFORE ADDING NEW FUNCTIONS TO THIS FILE, PLEASE CONSIDER CREATING A NEW SERVICE CLASS.
 * And test it. 👉👈
 */

/**
 * @see https://stackoverflow.com/a/437642
 */
function number($number, $decimals = 2) {
    return number_format(
        $number,
        $decimals,
        __('dates.decimal_point'),
        __('dates.thousands_sep')
    );
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
    $timezone = auth()->user()->timezone ?? config('app.display_timezone');
    if ($iso) {
        return $time->tz($timezone)->isoFormat($format);
    }
    return $time->tz($timezone)->format($format);
}

function hasStationBoardTimezoneOffsetToUser(Collection $departures, User $user): bool {
    foreach ($departures as $departure) {
        if (!empty($departure?->cancelled) && $departure->cancelled) {
            continue;
        }
        $departureObject = \Carbon\Carbon::parse($departure->when);
        $userObject      = CarbonTimeZone::create($user->timezone);
        $referenceObject = \Carbon\Carbon::parse($departureObject->format('Y-m-d H:i:s'));

        return $departureObject->tz->toOffsetName($referenceObject) !== $userObject->toOffsetName($referenceObject);
    }

    return false;
}

function errorMessage(Exception|Error $exception, ?string $text = null): array|null|string {
    $text = $text ?? __('messages.exception.general');

    if (!$exception instanceof Referencable) {
        return $text;
    }

    return $text . ' ' . __('messages.exception.reference', ['reference' => $exception->reference]);
}
