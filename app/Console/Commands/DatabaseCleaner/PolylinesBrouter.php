<?php

namespace App\Console\Commands\DatabaseCleaner;

use App\Models\PolyLine;
use App\Models\Trip;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class PolylinesBrouter extends Command
{
    protected $signature = 'app:clean-db:polylines:brouter {--limit=10}';

    public function handle(): void {
        $limit          = (int) $this->option('limit');
        $polylineGroups = $this->fetchPolylineGroups($limit);
        $i              = 1;
        $cnt = count($polylineGroups);
        $this->info("Found $cnt polyline groups.");

        foreach ($polylineGroups as $group) {
            $parent_id = $group->parent_id;
            $total     = $group->total;
            $this->comment("[$i/$limit] Processing polyline with parent_id $parent_id. Total entries: $total");

            $polyline = $this->fetchAndUpdatePolyline($parent_id);
            $this->info("Setting all trips with parent_id $parent_id to use polyline with id $polyline->id.");
            $this->updateTrips($group->ids, $polyline);
            $i++;
        }
    }

    private function updateTrips(string $ids, Polyline $polyline): void {
        Trip::whereIn('polyline_id', explode(',', $ids))
            ->orderBy('id', 'desc')
            ->get()
            ->each(fn($trip) => $trip->update(['polyline_id' => $polyline->id]));
    }

    private function fetchAndUpdatePolyline(int $parent_id): Polyline {
        $polyline = Polyline::where('parent_id', $parent_id)->orderBy('id', 'desc')->first();
        $geoJson  = json_decode($polyline->polyline, true);
        foreach ($geoJson['features'] as $key => $feature) {
            if (empty($feature['properties'])) {
                continue;
            }
            if (isset($feature['properties']['departure_planned'])) {
                unset($geoJson['features'][$key]['properties']['departure_planned']);
            }
            if (isset($feature['properties']['arrival_planned'])) {
                unset($geoJson['features'][$key]['properties']['arrival_planned']);
            }
        }

        $polyline->update(['polyline' => json_encode($geoJson)]);

        return $polyline;
    }

    private function fetchPolylineGroups(int $limit = 10): Collection {
        return DB::table('poly_lines')
                 ->select(
                     'parent_id',
                     DB::raw('count(*) as total'),
                     DB::raw('group_concat(id) as ids')
                 )
                 ->where('parent_id', '!=', null)
                 ->groupBy('parent_id')
                 ->limit($limit)
                 ->having('total', '>', 1)
                 ->get();
    }

}
