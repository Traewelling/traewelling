<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ReduceRelevance extends Command
{
    protected $signature = 'trwl:reduce-relevance';

    protected $description = 'This command divides the relevance of all stations by 10 to reduce their importance and prevent overflow.';

    public function handle(): void
    {
        $this->info('Reducing relevance of all stations by 10...');

        DB::table('train_stations')
            ->where('relevance', '>', 0)
            ->update(['relevance' => DB::raw('relevance / 10')]);

        $this->info('Relevance reduced successfully.');
    }
}
