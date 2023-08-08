<?php

namespace App\Http\Controllers\Backend\Stats;

use App\Http\Controllers\Controller;
use App\Models\Status;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class DailyStatsController extends Controller
{
    public static function getStatusesOnDate(User $user, Carbon $date): Collection {
        return Status::with(['trainCheckin'])
                     ->join('train_checkins', 'statuses.id', '=', 'train_checkins.status_id')
                     ->where('statuses.user_id', $user->id)
                     ->where('train_checkins.departure', '>=', $date->clone()->startOfDay())
                     ->where('train_checkins.departure', '<=', $date->clone()->endOfDay())
                     ->select('statuses.*')
                     ->get()
                     ->sortBy('trainCheckin.departure');
    }
}
