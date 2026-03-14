<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::table('route_segments', function (Blueprint $table): void {
            $table->index(
                ['from_station_id', 'to_station_id', 'polyline_hash'],
                'route_segments_polyline_lookup'
            );
        });
    }

    public function down(): void
    {
        Schema::table('route_segments', function (Blueprint $table): void {
            $table->dropIndex('route_segments_polyline_lookup');
        });
    }
};
