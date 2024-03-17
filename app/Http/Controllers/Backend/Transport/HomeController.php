<?php

namespace App\Http\Controllers\Backend\Transport;

use App\Http\Controllers\Controller;
use App\Models\Station;
use App\Models\User;

abstract class HomeController extends Controller
{
    /**
     * @param User    $user
     * @param Station $station
     *
     * @return Station
     * @api        v1
     * @deprecated just use $user->update(...) directly...?
     */
    public static function setHome(User $user, Station $station): Station {
        $user->update([
                          'home_id' => $station->id
                      ]);
        return $station;
    }
}
