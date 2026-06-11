<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Re-adds a unique constraint on (user_id, trip_id, origin_stopover_id) to prevent
 * duplicate checkins. The old constraint (user_trip_origin_departure) was dropped in
 * 2024_05_22_000003 when the legacy `origin` column was removed.
 *
 * Before adding the constraint, existing duplicate rows are cleaned up: for each
 * duplicate group the oldest checkin (lowest id) is kept; newer duplicates and their
 * corresponding statuses are deleted.
 */
return new class() extends Migration
{
    public function up(): void
    {
        $duplicates = DB::select(
            'SELECT MIN(id) AS keep_id, user_id, trip_id, origin_stopover_id
             FROM train_checkins
             WHERE origin_stopover_id IS NOT NULL
             GROUP BY user_id, trip_id, origin_stopover_id
             HAVING COUNT(*) > 1'
        );

        foreach ($duplicates as $duplicate) {
            $toDelete = DB::table('train_checkins')
                ->where('user_id', $duplicate->user_id)
                ->where('trip_id', $duplicate->trip_id)
                ->where('origin_stopover_id', $duplicate->origin_stopover_id)
                ->where('id', '!=', $duplicate->keep_id)
                ->pluck('status_id');

            DB::table('statuses')
                ->whereIn('id', $toDelete)
                ->delete();
        }

        Schema::table('train_checkins', static function (Blueprint $table) {
            $table->unique(
                ['user_id', 'trip_id', 'origin_stopover_id'],
                'user_trip_origin_stopover_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::table('train_checkins', static function (Blueprint $table) {
            $table->dropUnique('user_trip_origin_stopover_unique');
        });
    }
};
