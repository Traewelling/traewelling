<?php
function get_git_HEAD() {
    if ($head = file_get_contents(base_path() . '/.git/HEAD')) {
        return substr($head, 5, -1);
    } else {
        return false;
    }
}

function get_current_git_commit() {

    if ($hash = file_get_contents(base_path() . '/.git/' . get_git_HEAD())) {
        return $hash;
    } else {
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


function percentage_now_to_trip_duration($trainCheckin) {
    $departure = strtotime($trainCheckin->departure);
    $arrival = strtotime($trainCheckin->arrival);

    return 100 * (time() - $departure) / ($arrival - $departure);

}