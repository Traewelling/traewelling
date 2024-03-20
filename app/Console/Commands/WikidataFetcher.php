<?php

namespace App\Console\Commands;

use App\Http\Controllers\Backend\Wikidata\WikidataFetchController;
use Illuminate\Console\Command;

class WikidataFetcher extends Command
{
    protected $signature = 'app:wikidata-fetcher';

    public function handle(): void {
        WikidataFetchController::fetchOldestEntity();
    }
}
