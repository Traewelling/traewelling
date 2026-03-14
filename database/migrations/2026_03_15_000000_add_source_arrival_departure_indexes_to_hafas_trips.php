<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::table('hafas_trips', function (Blueprint $table): void {
            // Fixes Trip::groupBy('source'). Previously: full table scan + tmp table + filesort on 5.5M rows
            $table->index('source', 'hafas_trips_source_index');

            // Fixes active_statuses_count. Previously: full table scan on 5.5M rows
            // arrival first: arrival > NOW() selects only ~36 rows (very selective)
            $table->index(['arrival', 'departure'], 'hafas_trips_arrival_departure_index');
        });
    }

    public function down(): void
    {
        Schema::table('hafas_trips', function (Blueprint $table): void {
            $table->dropIndex('hafas_trips_source_index');
            $table->dropIndex('hafas_trips_arrival_departure_index');
        });
    }
};
