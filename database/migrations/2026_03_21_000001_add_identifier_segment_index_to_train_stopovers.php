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
            // Required for efficient lookup by identifier when assigning route segments.
            $table->index(
                ['station_identifier_id', 'route_segment_id'],
                'train_stopovers_identifier_segment_index',
            );
        });
    }

    public function down(): void
    {

        Schema::table('train_stopovers', function (Blueprint $table): void {
            $fkIndexName = 'train_stopovers_station_identifier_id_foreign';
            if (!Schema::hasIndex('train_stopovers', $fkIndexName)) {
                $table->index(['station_identifier_id'], $fkIndexName);
            }

            $table->dropIndex('train_stopovers_identifier_segment_index');
        });
    }
};
