<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeTrainStationTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('train_stations', function (Blueprint $table) {
            $table->decimal('latitude', 9, 6)->change();
            $table->decimal('longitude', 9, 6)->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('train_stations', function (Blueprint $table) {
            $table->decimal('latitude', 8, 6)->change();
            $table->decimal('longitude', 8, 6)->change();
        });
    }
}
