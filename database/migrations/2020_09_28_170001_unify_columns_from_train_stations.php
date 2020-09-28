<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UnifyColumnsFromTrainStations extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::table('train_stations', function (Blueprint $table) {
            $table->bigInteger('ibnr')->unsigned()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::table('train_stations', function (Blueprint $table) {
            $table->string('ibnr', 255)->change();
        });
    }
}
