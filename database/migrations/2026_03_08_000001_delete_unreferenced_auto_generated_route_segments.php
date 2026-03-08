<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Deletes auto-generated route segments (no custom_waypoints) that are no longer
 * referenced by any stopover. Runs after the stopover cleanup migration.
 */
return new class() extends Migration
{
    public function up(): void
    {
        $deleted = DB::table('route_segments')
            ->whereNull('custom_waypoints')
            ->whereNotExists(function ($query): void {
                $query->select(DB::raw(1))
                    ->from('train_stopovers')
                    ->whereNotNull('route_segment_id')
                    ->whereColumn('train_stopovers.route_segment_id', 'route_segments.id');
            })
            ->delete();

        Log::info('Cleanup: unreferenced auto-generated route segments deleted', ['count' => $deleted]);
    }

    public function down(): void
    {
        // This migration is irreversible — data cannot be restored after cleanup.
    }
};
