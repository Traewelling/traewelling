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
    public bool   $shouldPostOnTwitter;
    public bool   $shouldPostOnMastodon;

    /**
     * @param Status $status               The Status that was just checked in.
     * @param bool   $shouldPostOnTwitter  Whether this Checkin should be posted on Twitter.
     * @param bool   $shouldPostOnMastodon Whether this Checkin should be posted on Mastodon.
     */
    public function __construct(Status $status, bool $shouldPostOnTwitter, bool $shouldPostOnMastodon) {
        $this->status               = $status;
        $this->shouldPostOnTwitter  = $shouldPostOnTwitter;
        $this->shouldPostOnMastodon = $shouldPostOnMastodon;

        Log::info("Dispatching UserCheckedIn event for status#" . $status->id);
    }
}
