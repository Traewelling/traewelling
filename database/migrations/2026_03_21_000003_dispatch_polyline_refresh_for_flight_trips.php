<?php

declare(strict_types=1);

use App\Jobs\RefreshPolyline;
use App\Models\Trip;
use Illuminate\Database\Migrations\Migration;

return new class() extends Migration
{
    public function up(): void
    {
        Trip::where('category', 'plane')->chunkById(100, function ($trips): void {
            foreach ($trips as $trip) {
                RefreshPolyline::dispatch($trip);
            }
        });
    }
};
