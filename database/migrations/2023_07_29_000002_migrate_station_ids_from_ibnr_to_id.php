<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void {
        //drop old columns
        Schema::table('train_checkins', static function(Blueprint $table) {
            if (!App::runningUnitTests()) {
                $table->dropForeign(['origin']);
                $table->dropForeign(['destination']);
            }
            $table->dropIndex('user_trip_origin_departure');
            $table->dropColumn(['origin', 'destination']);
        });
    }

    public function down(): void {
        Schema::table('train_checkins', static function(Blueprint $table) {
            //add new columns
            $table->unsignedBigInteger('origin')
                  ->nullable()
                  ->after('origin_id');
            $table->unsignedBigInteger('destination')
                  ->nullable()
                  ->after('destination_id');

            //add foreign keys
            $table->foreign('origin')
                  ->references('ibnr')
                  ->on('train_stations');

            $table->foreign('destination')
                  ->references('ibnr')
                  ->on('train_stations');

            //add indexes
            $table->index(['user_id', 'trip_id', 'origin', 'destination'], 'user_trip_origin_departure');
        });
    }
};
