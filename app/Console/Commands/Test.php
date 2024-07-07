<?php

namespace App\Console\Commands;

use App\Services\Wikidata\StationFetcher;
use Illuminate\Console\Command;

class Test extends Command
{
    protected $signature = 'app:ttest';

    public function handle(): void {
        StationFetcher::test();
    }
}
