<?php

namespace App\Jobs;

use App\Exceptions\NotConnectedException;
use App\Http\Controllers\Backend\Social\AbstractTwitterController;
use App\Models\Status;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use romanzipp\QueueMonitor\Traits\IsMonitored;

class PostStatusOnTwitter implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, IsMonitored, Queueable, SerializesModels;

    protected Status $status;

    public function __construct(Status $status) {
        $this->status = $status;
    }

    /**
     * Execute the job.
     *
     * @return void
     * @throws \Exception
     */
    public function handle(): void {
        $this->queueData([
                             "status_id" => $this->status->id
                         ]);

        AbstractTwitterController::postStatus($this->status);
    }

    /**
     * Seconds until the job is retried after an error.
     */
    public function backoff() {
        return [10, 60, 5*60, 15*60, 60*60, 3*60*60, 6*60*60];
    }
    public $tries = 8; // count(backoff()) + 1 from the first attempt.
}
