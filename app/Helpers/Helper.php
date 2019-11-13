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

/**
 * @see https://stackoverflow.com/a/437642
 */
function number($number, $decimals=2) {
    return number_format($number, $decimals,
               __('dates.decimal_point'),
               __('dates.thousands_sep'));
 }