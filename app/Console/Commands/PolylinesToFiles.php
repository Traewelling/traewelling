<?php

namespace App\Console\Commands;

use App\Models\PolyLine;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PolylinesToFiles extends Command
{
    protected $signature   = 'app:polylines-to-files';
    protected $description = 'Convert polylines to files';

    public function handle(): int {
        $start = microtime(true);
        $rows  = DB::table('poly_lines')
                   ->where('polyline', '!=', '{}')
                   ->get();
        $this->info('Found ' . $rows->count() . ' polylines.');
        $affectedRows = 0;

        // get 100 rows at a time
        foreach ($rows->chunk(100) as $chunk) {
            $ids          = $chunk->pluck('id')->toArray();
            $affectedRows += PolyLine::whereIn('id', $ids)->get()->map(function($polyline) {
                $polyline->polyline; // trigger the __get method
                return $polyline;
            })->count();
            $this->output->write('.');
        }
        $this->output->newLine();

        $time_elapsed_secs = microtime(true) - $start;
        Log::debug($affectedRows . ' polylines converted in ' . $time_elapsed_secs . ' seconds.');
        $this->info($affectedRows . ' polylines converted in ' . $time_elapsed_secs . ' seconds.');
        return 0;
    }
}
