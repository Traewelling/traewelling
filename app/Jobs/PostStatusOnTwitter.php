<?php

namespace App\Jobs;

use App\Exceptions\NotConnectedException;
use App\Http\Controllers\Backend\Social\TwitterController;
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
     * @throws NotConnectedException
     */
    public function handle(): void {
        $this->queueData([
                             "status_id" => $this->status->id
                         ]);

        TwitterController::postStatus($this->status);
    }
}
