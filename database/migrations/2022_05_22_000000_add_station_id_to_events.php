<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up(): void {
        Schema::table('events', static function(Blueprint $table) {
            $table->unsignedBigInteger('station_id')->nullable()->after('trainstation');
            $table->foreign('station_id')->references('id')->on('train_stations');
        });

        DB::table('events')->update([
                                        'station_id' => DB::raw('trainstation'),
                                    ]);

        Schema::table('events', static function(Blueprint $table) {
            $table->dropForeign(['trainstation']);
            $table->dropColumn(['trainstation']);
        });
    }

    public function down(): void {
        Schema::table('events', static function(Blueprint $table) {
            $table->unsignedBigInteger('trainstation')->nullable()->after('station_id');
            $table->foreign('trainstation')->references('id')->on('train_stations');
        });

        DB::table('events')->update([
                                        'trainstation' => DB::raw('station_id'),
                                    ]);

        Schema::table('events', static function(Blueprint $table) {
            $table->dropForeign(['station_id']);
            $table->dropColumn(['station_id']);
        });
    }
};
