<?php

namespace App\Jobs;

use romanzipp\QueueMonitor\Traits\IsMonitored;
use Spatie\PersonalDataExport\ExportsPersonalData;
use Spatie\PersonalDataExport\Jobs\CreatePersonalDataExportJob;

class MonitoredPersonalDataExportJob extends CreatePersonalDataExportJob
{

    use IsMonitored;

    public $timeout = 30 * 60;


    protected function ensureValidUser(ExportsPersonalData $user) {
        // Do nothing since we are not enforcing the user to have an email property
    }
}
