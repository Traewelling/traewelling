<?php

namespace App\Http\Controllers\Backend\Admin;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use stdClass;

abstract class DashboardController extends Controller
{
    public static function getStatusesByDate(Carbon $since, Carbon $until): Collection {
        $data = DB::table('statuses')
                  ->where('created_at', '>=', $since->toIso8601String())
                  ->where('created_at', '<=', $until->toIso8601String())
                  ->groupBy(DB::raw('DATE(created_at)'))
                  ->select([
                               DB::raw('DATE(created_at) AS date'),
                               DB::raw('COUNT(*) AS count'),
                           ])
                  ->get();

        self::fillDates($since, $until, $data, ['count' => 0]);

        return $data->map(function($row) {
            $row->date = Carbon::parse($row->date);
            return $row;
        })->sortBy('date');
    }

    public static function getRegistrationsByDate(Carbon $since, Carbon $until): Collection {
        $data = DB::table('users')
                  ->where('created_at', '>=', $since->toIso8601String())
                  ->where('created_at', '<=', $until->toIso8601String())
                  ->whereNotNull('privacy_ack_at')
                  ->groupBy(DB::raw('DATE(created_at)'))
                  ->select([
                               DB::raw('DATE(created_at) AS date'),
                               DB::raw('COUNT(*) AS count'),
                           ])
                  ->get();

        self::fillDates($since, $until, $data, ['count' => 0]);

        return $data->map(function($row) {
            $row->date = Carbon::parse($row->date);
            return $row;
        })->sortBy('date');
    }

    public static function getTransportTypesByDate(Carbon $since, Carbon $until): Collection {
        $data = DB::table('statuses')
                  ->join('train_checkins', 'statuses.id', '=', 'train_checkins.status_id')
                  ->join('hafas_trips', 'hafas_trips.trip_id', '=', 'train_checkins.trip_id')
                  ->where('train_checkins.departure', '>=', $since->toIso8601String())
                  ->where('train_checkins.departure', '<=', $until->toIso8601String())
                  ->groupBy([
                                DB::raw('DATE(train_checkins.departure)'),
                                'hafas_trips.category',
                            ])
                  ->select([
                               DB::raw('DATE(train_checkins.departure) AS date'),
                               'hafas_trips.category',
                               DB::raw('COUNT(*) AS count'),
                           ])
                  ->get()
                  ->groupBy('category');

        foreach ($data as $category => $rows) {
            for ($date = $since->clone(); $date->isBefore($until); $date->addDay()) {
                if (!$rows->contains('date', $date->toDateString())) {
                    $row           = new stdClass();
                    $row->date     = $date->toDateString();
                    $row->category = $category;
                    $row->count    = 0;
                    $rows->push($row);
                }
            }

            $data->{$category} = $rows->sortBy('date')
                                      ->map(function($row) {
                                          $row->date = Carbon::parse($row->date);
                                          return $row;
                                      });
        }
        return $data;
    }

    public static function getHafasAndPolylinesByDate(Carbon $since, Carbon $until): Collection {
        $polyLineData = DB::table('poly_lines')
                          ->where('created_at', '>=', $since->toIso8601String())
                          ->where('created_at', '<=', $until->toIso8601String())
                          ->groupBy(DB::raw('DATE(created_at)'))
                          ->select([
                                       DB::raw('DATE(created_at) AS date'),
                                       DB::raw('COUNT(*) AS polyLineCount'),
                                   ])
                          ->get();
        $data         = DB::table('hafas_trips')
                          ->where('created_at', '>=', $since->toIso8601String())
                          ->where('created_at', '<=', $until->toIso8601String())
                          ->groupBy(DB::raw('DATE(created_at)'))
                          ->orderBy('date')
                          ->select([
                                       DB::raw('DATE(created_at) AS date'),
                                       DB::raw('COUNT(*) AS hafasTripsCount'),
                                   ])
                          ->get()
                          ->map(function($row) use ($polyLineData) {
                              $row->polyLineCount = $polyLineData->where('date', $row->date)->first()?->polyLineCount;
                              return $row;
                          });

        self::fillDates($since, $until, $data, ['hafasTripsCount' => 0, 'polyLineCount' => 0]);

        return $data->map(function($row) {
            $row->date = Carbon::parse($row->date);
            return $row;
        })->sortBy('date');
    }

    private static function fillDates(Carbon $since, Carbon $until, $data, array $exampleData = [], string $dateKey = 'date') {
        for ($date = $since->clone(); $date->isBefore($until); $date->addDay()) {
            if (!$data->contains($dateKey, $date->toDateString())) {
                $row             = new stdClass();
                $row->{$dateKey} = $date->toDateString();
                foreach ($exampleData as $key => $value) {
                    $row->{$key} = $value;
                }
                $data->push($row);
            }
        }
    }
}
