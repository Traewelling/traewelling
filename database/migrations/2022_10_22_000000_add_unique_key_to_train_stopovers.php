<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up(): void {
        Schema::table('train_stopovers', static function(Blueprint $table) {
            $table->unique(['trip_id', 'train_station_id', 'arrival_planned'], 'stopovers_station_arrival');
            $table->unique(['trip_id', 'train_station_id', 'departure_planned'], 'stopovers_station_departure');
        });
    }

    public function down(): void {
        Schema::table('train_stopovers', static function(Blueprint $table) {
            $table->dropUnique('stopovers_station_arrival');
            $table->dropUnique('stopovers_station_departure');
        });
    }
};
