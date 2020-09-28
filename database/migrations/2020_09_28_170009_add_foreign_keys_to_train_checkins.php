<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddForeignKeysToTrainCheckins extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::table('train_checkins', function (Blueprint $table) {
            $table->bigInteger('status_id')->unsigned()->change();
            $table->bigInteger('origin')->unsigned()->change();
            $table->bigInteger('destination')->unsigned()->change();

            $table->foreign('status_id')
                ->references('id')
                ->on('statuses')
                ->onDelete('cascade')
                ->onUpdate('cascade');
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
            $table->foreign('trip_id')
                ->references('trip_id')//TODO: Should use 'id' instead...
                ->on('hafas_trips')
                ->onDelete('restrict')
                ->onUpdate('restrict');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::table('train_checkins', function (Blueprint $table) {
            $table->dropForeign("train_checkins_origin_foreign");
            $table->dropForeign("train_checkins_destination_foreign");
            $table->dropForeign("train_checkins_trip_id_foreign");
            $table->dropForeign("train_checkins_status_id_foreign");
        });
        Schema::table('train_checkins', function (Blueprint $table) {
            $table->integer('status_id')->change();
            $table->string('origin', 255)->change();
            $table->string('destination', 255)->change();
        });
    }
}
