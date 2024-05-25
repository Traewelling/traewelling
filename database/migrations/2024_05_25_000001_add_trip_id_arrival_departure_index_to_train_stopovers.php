<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void {
        Schema::table('train_stopovers', static function(Blueprint $table) {
            $table->index(['trip_id', 'arrival_planned', 'departure_planned'], 'index_trip_id_arrival_departure');
        });
    }

    public function down(): void {
        Schema::table('train_stopovers', static function(Blueprint $table) {
            $table->dropIndex('index_trip_id_arrival_departure');
        });
    }
};
