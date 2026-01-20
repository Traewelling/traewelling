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
use Illuminate\Support\Facades\Log;
use romanzipp\QueueMonitor\Traits\IsMonitored;

class PostStatusOnMastodon implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, IsMonitored, Queueable, SerializesModels;

    protected Status $status;

    protected bool $shouldChain;

    // HTTP status codes that should not be retried (permanent errors)
    private const PERMANENT_ERROR_CODES = [401, 404, 410, 422];

    public function __construct(Status $status, bool $shouldChain)
    {
        $this->status = $status;
        $this->shouldChain = $shouldChain;
    }

    /**
     * Execute the job.
     *
     * @throws Exception|GuzzleException
     */
    public function handle(): void
    {
        $this->queueData([
            'status_id' => $this->status->id,
            'should_chain' => $this->shouldChain,
        ]);

        try {
            MastodonController::postStatus($this->status, $this->shouldChain);
        } catch (GuzzleException $e) {
            $code = $e->getCode();

            // Don't retry permanent errors (invalid auth, not found, validation errors)
            if (in_array($code, self::PERMANENT_ERROR_CODES)) {
                Log::warning(
                    "Permanent Mastodon posting error (HTTP {$code}) for status#{$this->status->id}. Not retrying."
                );
                // Mark job as failed without retry
                $this->fail($e);

                return;
            }

            // For temporary errors (5xx, timeouts, rate limits), throw to trigger retry
            Log::info(
                "Temporary Mastodon posting error (HTTP {$code}) for status#{$this->status->id}. "
                . "Will retry (attempt {$this->attempts()}/{$this->tries})."
            );
            throw $e;
        } catch (Exception $e) {
            // For non-HTTP exceptions, log and retry
            Log::warning(
                "Exception posting to Mastodon for status#{$this->status->id}: {$e->getMessage()}. "
                . "Will retry (attempt {$this->attempts()}/{$this->tries})."
            );
            throw $e;
        }
    }

    /**
     * Seconds until the job is retried after an error.
     * Shorter backoff times since most errors are temporary server issues.
     */
    public function backoff(): array
    {
        return [30, 120, 300, 900]; // 30s, 2m, 5m, 15m
    }

    /**
     * Maximum number of attempts
     */
    public $tries = 5; // count(backoff()) + 1 from the first attempt
}
