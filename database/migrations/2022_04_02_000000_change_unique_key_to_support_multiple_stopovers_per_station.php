<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up(): void {
        Schema::table('train_stopovers', static function(Blueprint $table) {
            $table->dropUnique('train_stopovers_trip_id_train_station_id_unique');
            $table->unique(
                ['trip_id', 'train_station_id', 'arrival_planned', 'departure_planned'],
                'train_stopovers_trip_id_train_station_id_unique'
            );
        });
    }

    public function down(): void {
        Schema::table('train_stopovers', static function(Blueprint $table) {
            $table->dropUnique('train_stopovers_trip_id_train_station_id_unique');
            $table->unique(
                ['trip_id', 'train_station_id'],
                'train_stopovers_trip_id_train_station_id_unique'
            );
        });
    }
};
