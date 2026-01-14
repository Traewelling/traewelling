<?php

namespace App\Console\Commands;

use App\Services\OperatorService;
use Illuminate\Console\Command;

class RefreshOperatorMappings extends Command
{
    protected $signature = 'app:refresh-operator-mappings';

    public function handle(): void
    {
        $operatorService = new OperatorService();
        $operatorService->refreshFiles();
    }
}
