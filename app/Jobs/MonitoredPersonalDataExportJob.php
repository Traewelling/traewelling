<?php

namespace App\Jobs;

use romanzipp\QueueMonitor\Traits\IsMonitored;
use Spatie\PersonalDataExport\ExportsPersonalData;
use Spatie\PersonalDataExport\Jobs\CreatePersonalDataExportJob;

class MonitoredPersonalDataExportJob extends CreatePersonalDataExportJob
{
    use IsMonitored;

    public int|float $timeout = 30 * 60;

    public int|float $tries = 3;

    public function __construct(ExportsPersonalData $user)
    {
        $this->timeout = config('trwl.gdpr_export.timeout', 30 * 60);
        $this->tries = config('trwl.gdpr_export.tries', 3);
        $this->onQueue('low');
        parent::__construct($user);
    }

    protected function ensureValidUser(ExportsPersonalData $user)
    {
        // Do nothing since we are not enforcing the user to have an email property
    }
}
