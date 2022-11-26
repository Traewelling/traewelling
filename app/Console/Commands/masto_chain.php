<?php

namespace App\Console\Commands;

use App\Http\Controllers\Backend\Social\MastodonController;
use App\Models\Status;
use Illuminate\Console\Command;

class masto_chain extends Command
{
    protected $signature = 'trwl:masto {status_id}';

    public function handle()
    {
        $statusId = $this->argument('status_id');
        $status   = Status::where('id', $statusId)->firstOrFail();

        dd(MastodonController::getEndOfChain($status->user, $status->mastodon_post_id));

        return Command::SUCCESS;
    }
}
