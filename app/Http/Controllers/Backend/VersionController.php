<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Exception;

abstract class VersionController extends Controller
{
    public static function getVersion(): bool|string {
        if (file_exists(base_path() . '/VERSION')) {
            return trim(file_get_contents(base_path() . '/VERSION'));
        }
        return trim(self::getCurrentGitCommit());
    }

    private static function get_git_HEAD(): bool|string {
        if ($head = @file_get_contents(base_path() . '/.git/HEAD')) {
            return substr($head, 5, -1);
        }
        return false;
    }

    private static function getCurrentGitCommit(): bool|string {
        try {
            if ($hash = @file_get_contents(base_path() . '/.git/' . self::get_git_HEAD())) {
                return $hash;
            }
        } catch (Exception $exception) {
            report($exception);
        }
        return false;
    }
}
