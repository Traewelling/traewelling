<?php

namespace App\Console\Commands;

use App\Http\Controllers\Backend\User\AccountDeletionController;
use Illuminate\Console\Command;

class AccountDeletion extends Command
{

    protected $signature   = 'app:account-deletion';
    protected $description = 'Send notifications about account deletion and delete inactive accounts.';

    public function handle(): void {
        AccountDeletionController::sendAccountDeletionNotificationTwoWeeksBefore();
        AccountDeletionController::deleteInactiveUsers();
    }
}
