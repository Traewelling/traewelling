<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up(): void {
        Schema::table('train_stopovers', static function(Blueprint $table) {
            $table->index(['trip_id', 'train_station_id', 'arrival_planned', 'departure_planned'], 'trip_station_time');
        });
    }

    public function down(): void {
        Schema::table('train_stopovers', static function(Blueprint $table) {
            $table->dropIndex('trip_station_time');
        });
    }
};
