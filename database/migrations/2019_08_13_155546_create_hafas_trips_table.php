<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateHafasTripsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('hafas_trips', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('trip_id');
            $table->string('category');
            $table->string('number');
            $table->string('linename');
            $table->integer('origin')
                ->references('ibnr')->on('train_stations');
            $table->integer('destination')
                ->reference('ibnr')->on('train_stations');
            $table->json('stopovers');
            $table->json('polyline');
            $table->timestampTz('departure');
            $table->timestampTz('arrival');
            $table->integer('delay');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('hafas_trips');
    }
}
