<?php

namespace App\Listeners;

use App\Events\UserCheckedIn;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendStatusWebhook implements ShouldQueue
{

    public function handle(UserCheckedIn $event): void {
        //TODO
        dd('Webhook senden');
    }
}
