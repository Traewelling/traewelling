<?php

namespace App\Http\Controllers\Backend\Transport;

use App\Http\Controllers\Controller;
use App\Models\TrainStation;
use App\Models\User;

abstract class HomeController extends Controller
{
    /**
     * @param User         $user
     * @param TrainStation $trainStation
     *
     * @return TrainStation
     * @api v1
     */
    public static function setTrainHome(User $user, TrainStation $trainStation): TrainStation {
        $user->update([
                          'home_id' => $trainStation->id
                      ]);
        return $trainStation;
    }
}
