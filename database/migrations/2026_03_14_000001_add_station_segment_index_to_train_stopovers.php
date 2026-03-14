<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::table('train_stopovers', function (Blueprint $table): void {
            $table->index(['train_station_id', 'route_segment_id'], 'train_stopovers_station_segment_index');
        });
    }

    public function down(): void
    {
        Schema::table('train_stopovers', function (Blueprint $table): void {
            $table->dropIndex('train_stopovers_station_segment_index');
        });
    }
};
