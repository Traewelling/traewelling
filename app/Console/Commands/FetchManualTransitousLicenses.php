<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class FetchManualTransitousLicenses extends Command
{
    protected $signature = 'trwl:fetch-manual-transitous-licenses';

    protected $description = 'Dispatches the job to fetch the manual license definitions from our license repository.';

    public function handle()
    {
        $this->info('Dispatching job to fetch manual transitous licenses...');
        \App\Jobs\FetchManualTransitousLicenses::dispatch();
        $this->info('Job dispatched successfully.');
    }
}
