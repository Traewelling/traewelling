<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\User;
use App\TrainCheckin;
use Illuminate\Support\Facades\DB;

class AppStatusController extends Controller
{

    private static function lastDay($q) {
        return $q->where('created_at', '<', 'NOW() - INTERVAL 1 DAY');
    }
    private static function dayBefore($q) {
        return $q->whereBetween('created_at', ['NOW() - INTERVAL 1 DAY', 'NOW() - INTERVAL 2 DAY']);
    } 

    private static function lastWeek($q) {
        return $q->where('created_at', '<', 'NOW() - INTERVAL 1 WEEK');
    }
    private static function weekBefore($q) {
        return $q->whereBetween('created_at', ['NOW() - INTERVAL 1 WEEK', 'NOW() - INTERVAL 2 WEEK']);
    } 
    private static function lastMonth($q) {
        return $q->where('created_at', '<', 'NOW() - INTERVAL 1 MONTH');
    } 
    private static function monthBefore($q) {
        return $q->whereBetween('created_at', ['NOW() - INTERVAL 1 MONTH', 'NOW() - INTERVAL 2 MONTH']);
    } 

    public function appStatus() {

        $users = User::all();
        $trips = TrainCheckin::all();

        return view('appstatus', [
            'all_users' => $users->count(),
            'users_last_week' => self::lastWeek($users)->count(),

            'all_trips' => TrainCheckin::all()->count(),
            'trips_last_day' => TrainCheckin::whereRaw('created_at > NOW() - INTERVAL 1 DAY')->count(),
            'trips_day_before' => TrainCheckin::whereRaw('created_at < NOW() - INTERVAL 1 DAY AND created_at > NOW() - INTERVAL 2 DAY')->count(),
            'trips_last_week' => TrainCheckin::whereRaw('created_at > NOW() - INTERVAL 1 WEEK')->count(),
            'trips_week_before' => TrainCheckin::whereRaw('created_at < NOW() - INTERVAL 1 WEEK AND created_at > NOW() - INTERVAL 2 WEEK')->count(),
            'trips_last_month' => TrainCheckin::whereRaw('created_at > NOW() - INTERVAL 1 MONTH')->count(),
            'trips_month_before' => TrainCheckin::whereRaw('created_at < NOW() - INTERVAL 1 MONTH AND created_at > NOW() - INTERVAL 2 MONTH')->count(),

            'distance_last_day' => TrainCheckin::whereRaw('created_at > NOW() - INTERVAL 1 DAY')->sum('distance'),
            'distance_day_before' => TrainCheckin::whereRaw('created_at < NOW() - INTERVAL 1 DAY AND created_at > NOW() - INTERVAL 2 DAY')->sum('distance'),
            'distance_last_week' => TrainCheckin::whereRaw('created_at > NOW() - INTERVAL 1 WEEK')->sum('distance'),
            'distance_week_before' => TrainCheckin::whereRaw('created_at < NOW() - INTERVAL 1 WEEK AND created_at > NOW() - INTERVAL 2 WEEK')->sum('distance'),
            'distance_last_month' => TrainCheckin::whereRaw('created_at > NOW() - INTERVAL 1 MONTH')->sum('distance'),
            'distance_month_before' => TrainCheckin::whereRaw('created_at < NOW() - INTERVAL 1 MONTH AND created_at > NOW() - INTERVAL 2 MONTH')->sum('distance'),

            'time_last_day' => TrainCheckin::select(DB::raw('SUM(TIME_TO_SEC(arrival) - TIME_TO_SEC(departure)) AS timediff'))->whereRaw('created_at > NOW() - INTERVAL 1 DAY')->get()[0]["timediff"],
            'time_day_before' => TrainCheckin::select(DB::raw('SUM(TIME_TO_SEC(arrival) - TIME_TO_SEC(departure)) AS timediff'))->whereRaw('created_at < NOW() - INTERVAL 1 DAY AND created_at > NOW() - INTERVAL 2 DAY')->get()[0]["timediff"],
            'time_last_week' => TrainCheckin::select(DB::raw('SUM(TIME_TO_SEC(arrival) - TIME_TO_SEC(departure)) AS timediff'))->whereRaw('created_at > NOW() - INTERVAL 1 WEEK')->get()[0]["timediff"],
            'time_week_before' => TrainCheckin::select(DB::raw('SUM(TIME_TO_SEC(arrival) - TIME_TO_SEC(departure)) AS timediff'))->whereRaw('created_at < NOW() - INTERVAL 1 WEEK AND created_at > NOW() - INTERVAL 2 WEEK')->get()[0]["timediff"],
            'time_last_month' => TrainCheckin::select(DB::raw('SUM(TIME_TO_SEC(arrival) - TIME_TO_SEC(departure)) AS timediff'))->whereRaw('created_at > NOW() - INTERVAL 1 MONTH')->get()[0]["timediff"],
            'time_month_before' => TrainCheckin::select(DB::raw('SUM(TIME_TO_SEC(arrival) - TIME_TO_SEC(departure)) AS timediff'))->whereRaw('created_at < NOW() - INTERVAL 1 MONTH AND created_at > NOW() - INTERVAL 2 MONTH')->get()[0]["timediff"],

            
        ]);
    }
}
