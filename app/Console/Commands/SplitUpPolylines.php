<?php

namespace App\Console\Commands;

use App\Exceptions\DistanceDeviationException;
use App\Http\Controllers\Backend\Transport\TrainCheckinController;
use App\Http\Controllers\Locations\LineRunController;
use App\Models\LineRun;
use App\Models\PolyLine;
use App\Models\Status;
use Illuminate\Console\Command;

class SplitUpPolyLines extends Command
{
    protected $signature   = 'trwl:splitUpPolyLines {id?*} {--reverse}';
    protected $description = 'Recalculate distance for status id';

    public function handle(): void {
        $ids = $this->arguments()['id'];
        if (empty($ids)) {
            $polyLines = PolyLine::whereNotIn('hash', LineRun::where('id', '>', 0)->groupBy('hash')->select('hash'))
                                 ->orderBy('id', $this->option('reverse') ? 'desc' : 'asc')
                                 ->limit(500)
                                 ->get();
        } else {
            $polyLines = PolyLine::whereIn('id', $ids)->get();
        }
        $this->info(sprintf('Found %d of %d poly lines', count($polyLines), count($ids)));
        $this->newLine(3);
        foreach ($polyLines as $key => $polyLine) {
            try {
                (new LineRunController(json_decode($polyLine->polyline), $polyLine->hash))->splitAndSaveLineRun();
                $this->info(sprintf('Split up #%s: %s', $key, $polyLine->hash));
            } catch (\Throwable $e) {
                $this->error(sprintf('Error splitting up #%s: %s', $key, $polyLine->hash));
                $this->error($e->getMessage());
            }
        }
    }
}
