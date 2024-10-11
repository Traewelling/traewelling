<?php

namespace App\Console\Commands\DatabaseCleaner;

use App\Models\PolyLine;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class Polylines extends Command
{
    protected $signature   = 'app:clean-db:polylines';
    protected $description = 'Find and delete unused and old polylines from database';

    public function handle(): int {
        $start = microtime(true);
        $rows  = DB::table('poly_lines')
                   ->selectRaw('poly_lines.id, poly_lines.parent_id')
                   ->leftJoin('hafas_trips', 'poly_lines.id', '=', 'hafas_trips.polyline_id')
                   ->leftJoin(
                       'poly_lines AS parent_poly_lines',
                       'poly_lines.id',
                       '=',
                       'parent_poly_lines.parent_id'
                   )
                   ->whereRaw('hafas_trips.polyline_id IS NULL AND parent_poly_lines.parent_id IS NULL')
                   ->get();
        $this->info('Found ' . $rows->count() . ' unused polylines.');
        $affectedRows = 0;

        // get 100 rows at a time
        foreach ($rows->chunk(100) as $chunk) {
            $ids = $chunk->pluck('id')->toArray();
            $row = PolyLine::whereIn('id', $ids)->get();
            foreach ($row as $polyline) {
                $polyline->delete();
                $affectedRows++;
            }
            $this->output->write('.');
        }
        $this->output->newLine();

        $time_elapsed_secs = microtime(true) - $start;
        Log::debug($affectedRows . ' unused polylines deleted in ' . $time_elapsed_secs . ' seconds.');
        $this->info($affectedRows . ' unused polylines deleted in ' . $time_elapsed_secs . ' seconds.');
        return 0;
    }
}
