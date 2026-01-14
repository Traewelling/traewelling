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
function number($number, $decimals = 0)
{
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
 * @param  int  $seconds  How long in seconds?
 * @return array with `hours`, `minutes` and `showHours`.
 */
function secondsToDuration(int $seconds): array
{
    return [
        'years' => intdiv($seconds, 3600 * 24 * 365),
        'days' => intdiv($seconds % (3600 * 24 * 365), 3600 * 24),
        'hours' => intdiv($seconds % (3600 * 24), 3600),
        'minutes' => intdiv($seconds % 3600, 60),
    ];
}

/**
 * @param  array  $duration  from the secondsToDuration
 */
function durationToSpan(array $duration): string
{
    $return = $duration['minutes'] . "<small class='text-muted'>min</small>";

    if ($duration['hours'] > 0 || $duration['days'] > 0 || $duration['years'] > 0) {
        $return = $duration['hours'] . "<small class='text-muted'>h</small>&nbsp;" . $return;
    }

    if ($duration['days'] > 0 || $duration['years'] > 0) {
        $return = $duration['days'] . "<small class='text-muted'>d</small>&nbsp;" . $return;
    }

    if ($duration['years'] > 0) {
        $return = $duration['years'] . "<small class='text-muted'>y</small>&nbsp;" . $return;
    }

    return $return;
}

function userTime(null|Carbon|\Carbon\Carbon|string $time = null, ?string $format = null, bool $iso = true): string
{
    if ($time === null) {
        return '';
    }
    $format = $format ?? __('time-format');
    $time = $time instanceof \Carbon\Carbon ? $time : Carbon::parse($time);
    $timezone = auth()->user()->timezone ?? config('app.display_timezone');
    if ($iso) {
        return $time->tz($timezone)->isoFormat($format);
    }

    return $time->tz($timezone)->format($format);
}

function hasStationBoardTimezoneOffsetToUser(Collection $departures, User $user): bool
{
    foreach ($departures as $departure) {
        if (!empty($departure?->cancelled) && $departure->cancelled) {
            continue;
        }
        $departureObject = \Carbon\Carbon::parse($departure->when);
        $userObject = CarbonTimeZone::create($user->timezone);
        $referenceObject = \Carbon\Carbon::parse($departureObject->format('Y-m-d H:i:s'));

        return $departureObject->tz->toOffsetName($referenceObject) !== $userObject->toOffsetName($referenceObject);
    }

    return false;
}

function errorMessage(Exception|Error $exception, ?string $text = null): array|null|string
{
    $text = $text ?? __('messages.exception.general');

    if (!$exception instanceof Referencable) {
        return $text;
    }

    return $text . ' ' . __('messages.exception.reference', ['reference' => $exception->reference]);
}
