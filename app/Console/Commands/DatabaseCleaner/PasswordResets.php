<?php

namespace App\Console\Commands\DatabaseCleaner;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class PasswordResets extends Command
{
    protected $signature   = 'app:clean-db:password-resets';
    protected $description = 'Delete expired password reset tokens';

    public function handle(): int {
        $this->info('Deleting expired password reset tokens...');
        $this->output->writeln('');
        $rowsAffected = 0;
        do {
            $result = DB::table('password_resets')
                              ->where('created_at', '<', Carbon::now()->subHour()->toIso8601String())
                              ->limit(1000)
                              ->delete();
            if ($rowsAffected > 0) {
                $this->output->write('.');
            }
            $rowsAffected += $result;
        } while ($result > 0);
        $this->output->writeln('');

        $this->info('Deleted ' . $rowsAffected . ' expired password reset tokens');
        return 0;
    }
}
