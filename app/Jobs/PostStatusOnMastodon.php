<?php

namespace App\Jobs;

use App\Http\Controllers\Backend\Social\MastodonController;
use App\Models\Status;
use Exception;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use romanzipp\QueueMonitor\Traits\IsMonitored;

class PostStatusOnMastodon implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, IsMonitored, Queueable, SerializesModels;

    protected Status $status;
    protected bool   $shouldChain;

    public function __construct(Status $status, bool $shouldChain) {
        $this->status      = $status;
        $this->shouldChain = $shouldChain;
    }

    /**
     * Execute the job.
     *
     * @return void
     * @throws Exception|GuzzleException
     */
    public function handle(): void {
        $this->queueData([
                             "status_id"    => $this->status->id,
                             "should_chain" => $this->shouldChain,
                         ]);

        MastodonController::postStatus($this->status, $this->shouldChain);
    }

    /**
     * Seconds until the job is retried after an error.
     */
    public function backoff() {
        return [10, 60, 5*60, 15*60, 60*60, 3*60*60, 6*60*60];
    }
    public $tries = 8; // count(backoff()) + 1 from the first attempt.
}
