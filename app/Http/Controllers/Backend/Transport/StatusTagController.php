<?php

namespace App\Http\Controllers\Backend\Transport;

use App\Http\Controllers\Controller;
use App\Models\Status;
use App\Models\StatusTag;
use App\Models\TrainStation;
use App\Models\TrainStopover;
use App\Models\User;
use Gate;

abstract class StatusTagController extends Controller
{
    public static function getVisibleTagsForUser(Status $status, User $user = null) {
        return $status->tags->filter(function(StatusTag $tag) use ($user) {
            return Gate::forUser($user)->allows('view', $tag);
        });
    }
}
