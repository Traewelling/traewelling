<?php

namespace App\Http\Controllers\Backend\Export;

use App\Http\Controllers\Controller;
use App\Models\Status;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;

abstract class ExportController extends Controller
{

    public static function getExportableStatuses(User $user, Carbon $from, Carbon $to): Collection {
        return Status::join('train_checkins', 'statuses.id', '=', 'train_checkins.status_id')
                     ->where('statuses.user_id', $user->id)
                     ->where('train_checkins.departure', '>=', $from->toIso8601String())
                     ->where('train_checkins.departure', '<=', $to->toIso8601String())
                     ->get();
    }

}
