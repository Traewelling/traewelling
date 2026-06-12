<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Adds a unique constraint on (user_id, trip_id, origin_stopover_id) to prevent
 * duplicate checkins. Before adding the constraint, existing duplicate rows are
 * cleaned up: for each group the oldest checkin (lowest id) is kept; newer
 * duplicates and their parent statuses are deleted (cascade removes the checkin rows).
 */
return new class() extends Migration
{
    public function up(): void
    {
        $duplicates = DB::table('train_checkins')
            ->select('user_id', 'trip_id', 'origin_stopover_id')
            ->selectRaw('MIN(id) AS keep_id')
            ->whereNotNull('origin_stopover_id')
            ->groupBy('user_id', 'trip_id', 'origin_stopover_id')
            ->having(DB::raw('COUNT(*)'), '>', 1)
            ->get();

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

        Schema::table('train_checkins', static function (Blueprint $table): void {
            $table->unique(
                ['user_id', 'trip_id', 'origin_stopover_id'],
                'user_trip_origin_stopover_unique',
            );
        });
    }

    public function down(): void
    {
        Schema::table('train_checkins', static function (Blueprint $table): void {
            $table->dropUnique('user_trip_origin_stopover_unique');
        });
    }
};
