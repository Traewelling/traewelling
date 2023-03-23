<?php

namespace App\Events;

use App\Models\Status;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class UserCheckedIn
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public Status $status;
    public bool   $shouldPostOnMastodon;
    public bool   $shouldChain;

    /**
     * @param Status $status               The Status that was just checked in.
     * @param bool   $shouldPostOnMastodon Whether this Checkin should be posted on Mastodon.
     * @param bool   $shouldChain          Whether the Checkin post should be chained to the last one on Mastodon.
     */
    public function __construct(Status $status, bool $shouldPostOnMastodon, bool $shouldChain) {
        $this->status               = $status;
        $this->shouldPostOnMastodon = $shouldPostOnMastodon;
        $this->shouldChain          = $shouldChain;

        Log::info("Dispatching UserCheckedIn event for status#" . $status->id);
    }
}
