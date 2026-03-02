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
            $table->dropIndex('route_segments_lookup');
        });
    }
};
