<?php
function get_git_HEAD() {
    if ($head = file_get_contents(base_path() . '/.git/HEAD')) {
        return substr($head, 5, -1);
    } else {
        return false;
    }
}

function get_current_git_commit() {
    try {
        if ($hash = file_get_contents(base_path() . '/.git/' . get_git_HEAD())) {
            return $hash;
        } else {
            return false;
        }
    } catch(Exception $e) {
        return false;
    }
}

function get_current_git_commit_message( $branch='master' ) {
    if ($message = file_get_contents(base_path() . '/.git/COMMIT_EDITMSG')) {
        return $message;
    } else {
        return false;
    }
}

/**
 * @see https://stackoverflow.com/a/437642
 */
function number($number, $decimals=2) {
    return number_format($number, $decimals,
               __('dates.decimal_point'),
               __('dates.thousands_sep'));
}

/**
 * Calculate hours and minutes from a given duration in seconds.
 *
 * @param int $seconds How long in seconds?
 * @return array with `hours`, `minutes` and `showHours`.
 */
function secondsToDuration($seconds): array {
    $secondsInAnHour = 60*60;

    return [
        "hours" => intdiv($seconds, $secondsInAnHour),
        "minutes" => intdiv($seconds % $secondsInAnHour, 60),
        "showHours" => $seconds > $secondsInAnHour
    ];
}

/**
 * @param array $duration from the secondsToDuration
 */
function durationToSpan($duration): String {
    $return = $duration["minutes"] . "<small>min</small>";

    if($duration["showHours"]) {
        $return = $duration["hours"] . "<small>h</small>&nbsp;" . $return;
    }

    return $return;
}

function nextStation(&$status) {
    $stops = json_decode($status->trainCheckin->HafasTrip->stopovers);
    $nextStopIndex = count($stops) - 1;

    // Wir rollen die Reise von hinten auf, damit der nächste Stop als letztes vorkommt.
    for ($i=count($stops)-1; $i > 0; $i--) {
        $arrival = $stops[$i]->arrival;
        if($arrival != null && strtotime($arrival) > time()) {
            $nextStopIndex = $i;
            continue;
        }
        break; // Wenn wir diesen Teil der Loop erreichen, kann die Loop beendert werden.
    }
    return $stops[$nextStopIndex]->stop->name;
}

function stationLink($name, $classes = "text-trwl clearfix"): String {
    $urlname = $name;

    switch($name) {
        // Those are stations that you can ride to but you can't search for them.
        case $name == "Köln Messe/Deutz Gl. 9-10":
        case $name == "Köln Messe/Deutz Gl.11-12":
            $urlname = "Köln Messe/Deutz";
        break;

        // Hamburg's Landungsbrücken has three bridges [1..3], but you cannot search for them.
        case preg_match('/Landungsbr.*cken Br.*cke \d/i', $name) > 0:
            $urlname = "Landungsbrücken, Hamburg";
        break;
    }

    $return = '<a href="' . route('trains.stationboard') . '?provider=train&station=' . urlencode($urlname) . '" class="' . $classes . '">' . $name . '</a>';

    return $return;
}

function formatNewDay($DateObject) {



    return __("dates." . $DateObject->format('l')) .', '. $DateObject->format('j').'. '.__("dates." . $DateObject->format('F')) .' '. $DateObject->format('Y');
}
