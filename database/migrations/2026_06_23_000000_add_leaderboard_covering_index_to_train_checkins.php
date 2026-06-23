<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::table('train_checkins', function (Blueprint $table) {
            // Covers the leaderboard GROUP BY query:
            //   SELECT user_id, SUM(points), SUM(distance), SUM(TIMESTAMPDIFF(departure, arrival))
            //   FROM train_checkins JOIN users ...
            //   WHERE departure BETWEEN ? AND ?
            //   GROUP BY user_id ORDER BY SUM(points) DESC
            //
            // Previously: full scan ~5.4M rows per call (10742s total, 142 calls).
            // This covering index lets the query be satisfied from index pages alone —
            // no table data page fetches needed for the train_checkins part.
            $table->index(
                ['departure', 'user_id', 'points', 'distance', 'arrival'],
                'train_checkins_leaderboard_covering_index'
            );
        });
    }

    public function down(): void
    {
        Schema::table('train_checkins', function (Blueprint $table) {
            $table->dropIndex('train_checkins_leaderboard_covering_index');
        });
    }
};
