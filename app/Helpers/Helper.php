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

function stationLink($name): String {
    $urlname = $name;
    
    switch($name) {
        // Those are stations that you can ride to but you can't search for them.
        case "Köln Messe/Deutz Gl. 9-10":
        case "Köln Messe/Deutz Gl.11-12":
            $urlname = "Köln Messe/Deutz";
        break;
    }

    $return = '<a href="' . route('trains.stationboard') . '?provider=train&station=' . urlencode($urlname) . '" class="text-trwl clearfix">' . $name . '</a>';

    return $return;
}