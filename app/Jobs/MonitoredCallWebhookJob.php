<?php

namespace App\Jobs;

use romanzipp\QueueMonitor\Traits\IsMonitored;
use Spatie\WebhookServer\CallWebhookJob;

class MonitoredCallWebhookJob extends CallWebhookJob
{
    use IsMonitored;
}
