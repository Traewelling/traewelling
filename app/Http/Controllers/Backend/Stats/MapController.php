<?php

namespace App\Http\Controllers\Backend\Stats;

use App\Enum\StatusVisibility;
use App\Http\Controllers\Backend\Geo\GeoJsonController;
use App\Http\Controllers\Controller;
use App\Models\PolyLine;
use App\Models\Status;
use App\Models\TrainCheckin;
use App\Models\TrainStation;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use stdClass;

abstract class MapController extends Controller
{

    public static function renderMap(User $user, Carbon $from, Carbon $to) {
        $data = DB::table('train_checkins')
                  ->join('hafas_trips', 'hafas_trips.trip_id', '=', 'train_checkins.trip_id')
                  ->where('user_id', $user->id)
                  ->whereNotNull('hafas_trips.polyline_id')
                  ->where('train_checkins.departure', '>', $from->toIso8601String())
                  ->where('train_checkins.departure', '<', $to->toIso8601String())
                  ->groupBy(['hafas_trips.category', 'hafas_trips.origin', 'hafas_trips.destination', 'train_checkins.origin', 'train_checkins.destination'])
                  ->select([
                               'train_checkins.origin',
                               'train_checkins.destination',
                               DB::raw('MAX(hafas_trips.polyline_id) AS polyline_id')
                           ])
                  ->get();

        $polylines = PolyLine::whereIn('id', $data->pluck('polyline_id'))->get();

        $data = $data->map(function(stdClass $item) use ($polylines) {
            $item->polyline = $polylines->where('id', $item->polyline_id)->first();

            dd(GeoJsonController::trimPolyline($item->polyline, TrainStation::where('ibnr', $item->origin)->first(), TrainStation::where('ibnr', $item->destination)->first()));
            unset($item->polyline_id);
            return $item;
        });

        dd($data);
    }
}
