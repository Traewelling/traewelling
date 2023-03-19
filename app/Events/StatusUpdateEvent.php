<?php

namespace App\Events;

use App\Models\Status;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class StatusUpdateEvent {
    use Dispatchable, SerializesModels;

    public Status $status;
    public function __construct(Status $status) {
        $this->status               = $status;
        Log::info("Dispatching StatusUpdateEvent event for status#" . $status->id);
    }
}
