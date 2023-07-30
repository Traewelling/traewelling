<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void {
        Schema::table('train_checkins', static function(Blueprint $table) {
            //add new columns
            $table->unsignedBigInteger('origin_id')
                  ->nullable()
                  ->after('origin');
            $table->unsignedBigInteger('destination_id')
                  ->nullable()
                  ->after('destination');

            //add foreign keys
            $table->foreign('origin_id')
                  ->references('id')
                  ->on('train_stations');

            $table->foreign('destination_id')
                  ->references('id')
                  ->on('train_stations');

            //add indexes - same as with the old attributes (origin, destination)
            $table->index(['user_id', 'trip_id', 'origin_id', 'destination_id'], 'train_checkins_user_trip_origin_destination_index');
        });
    }

    public function down(): void {
        Schema::table('train_checkins', static function(Blueprint $table) {
            $table->dropIndex('train_checkins_user_trip_origin_destination_index');
            $table->dropForeign(['origin_id']);
            $table->dropForeign(['destination_id']);
            $table->dropColumn(['origin_id', 'destination_id']);
        });
    }
};
