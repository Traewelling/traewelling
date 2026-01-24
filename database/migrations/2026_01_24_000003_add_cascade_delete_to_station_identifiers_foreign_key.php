<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('station_identifiers', function (Blueprint $table) {
            // Drop the existing foreign key
            $table->dropForeign(['station_id']);

            // Re-add it with cascade on delete
            $table->foreign('station_id')
                ->references('id')
                ->on('train_stations')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('station_identifiers', function (Blueprint $table) {
            // Drop the foreign key with cascade
            $table->dropForeign(['station_id']);

            // Re-add without cascade (original state)
            $table->foreign('station_id')
                ->references('id')
                ->on('train_stations');
        });
    }
};
