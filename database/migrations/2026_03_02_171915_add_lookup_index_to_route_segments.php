<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::table('route_segments', function (Blueprint $table) {
            // Query: WHERE from_station_id = ? AND to_station_id = ? AND path_type = ? AND duration BETWEEN ? AND ?
            $table->index(['from_station_id', 'to_station_id', 'path_type', 'duration'], 'route_segments_lookup');
        });
    }

    public function down(): void
    {
        Schema::table('route_segments', function (Blueprint $table) {
            // MariaDB refuses to drop the index while a FK uses it as its backing index
            // so... Drop the FK, remove the index, then restore the FK.
            $table->dropForeign(['from_station_id']);
            $table->dropIndex('route_segments_lookup');
            $table->foreign('from_station_id')->references('id')->on('train_stations')->cascadeOnDelete();
        });
    }
};
