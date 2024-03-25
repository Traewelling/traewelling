<?php

namespace App\Jobs;

use romanzipp\QueueMonitor\Traits\IsMonitored;
use Spatie\PersonalDataExport\Jobs\CreatePersonalDataExportJob;

class MonitoredPersonalDataExportJob extends CreatePersonalDataExportJob
{

    use IsMonitored;

    public $timeout = 30 * 60;

}
