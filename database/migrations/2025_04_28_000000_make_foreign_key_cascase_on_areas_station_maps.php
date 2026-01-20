<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::table('areas_stations_maps', function (Blueprint $table) {
            $table->dropForeign(['station_id']);
            $table->dropForeign(['area_id']);

            $table->foreign('station_id')
                ->references('id')
                ->on('train_stations')
                ->cascadeOnDelete();

            $table->foreign('area_id')
                ->references('id')
                ->on('areas')
                ->cascadeOnDelete();
        });
    }
};
