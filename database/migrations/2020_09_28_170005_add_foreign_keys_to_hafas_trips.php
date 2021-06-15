<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * @todo Squash to hafas_trips creation after resorting tables
 * Class AddForeignKeysToHafasTrips
 */
class AddForeignKeysToHafasTrips extends Migration
{

    public function up(): void {
        Schema::table('hafas_trips', function(Blueprint $table) {
            $table->foreign('origin')
                  ->references('ibnr')//TODO: Should use 'id' instead...
                  ->on('train_stations');
            $table->foreign('destination')
                  ->references('ibnr')//TODO: Should use 'id' instead...
                  ->on('train_stations');

            $table->foreign('polyline')
                  ->references('hash')//TODO: Should use 'id' instead...
                  ->on('poly_lines');
        });
    }

    public function down(): void {
        Schema::table('hafas_trips', function(Blueprint $table) {
            $table->dropForeign(['origin']);
            $table->dropForeign(['destination']);
            $table->dropForeign(['polyline']);
        });
    }
}
