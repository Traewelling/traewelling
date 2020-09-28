<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddForeignKeysToHafasTrips extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::table('hafas_trips', function (Blueprint $table) {
            $table->bigInteger('origin')->unsigned()->change();
            $table->bigInteger('destination')->unsigned()->change();

            $table->unique('trip_id');

            $table->foreign('origin')
                ->references('ibnr')//TODO: Should use 'id' instead...
                ->on('train_stations')
                ->onDelete('restrict')
                ->onUpdate('cascade');
            $table->foreign('destination')
                ->references('ibnr')//TODO: Should use 'id' instead...
                ->on('train_stations')
                ->onDelete('restrict')
                ->onUpdate('cascade');
            $table->foreign('polyline')
                ->references('hash')//TODO: Should use 'id' instead...
                ->on('poly_lines')
                ->onDelete('restrict')
                ->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::table('hafas_trips', function (Blueprint $table) {
            $table->dropUnique('hafas_trips_trip_id_unique');

            $table->dropForeign("hafas_trips_origin_foreign");
            $table->dropForeign("hafas_trips_destination_foreign");
            $table->dropForeign("hafas_trips_polyline_foreign");
        });
        Schema::table('hafas_trips', function (Blueprint $table) {
            $table->string('origin', 255)->change();
            $table->string('destination', 255)->change();
        });
    }
}
