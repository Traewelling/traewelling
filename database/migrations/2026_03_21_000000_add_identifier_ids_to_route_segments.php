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
            $table->uuid('from_identifier_id')->nullable()->after('from_station_id');
            $table->uuid('to_identifier_id')->nullable()->after('to_station_id');

            $table->foreign('from_identifier_id')
                ->references('id')
                ->on('station_identifiers')
                ->nullOnDelete();

            $table->foreign('to_identifier_id')
                ->references('id')
                ->on('station_identifiers')
                ->nullOnDelete();

            $table->index(['from_identifier_id', 'to_identifier_id'], 'route_segments_identifier_pair_index');
        });
    }

    public function down(): void
    {
        Schema::table('route_segments', function (Blueprint $table): void {
            $table->dropForeign(['from_identifier_id']);
            $table->dropForeign(['to_identifier_id']);
            $table->dropIndex('route_segments_identifier_pair_index');
            $table->dropColumn(['from_identifier_id', 'to_identifier_id']);
        });
    }
};
