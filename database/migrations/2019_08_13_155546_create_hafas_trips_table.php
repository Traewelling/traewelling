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
            $table->string('origin');
            $table->string('destination');
            $table->json('stopovers')->nullable();
            $table->json('polyline')->nullable();
            $table->timestampTz('departure')->nullable();
            $table->timestampTz('arrival')->nullable();
            $table->integer('delay')->nullable();
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
