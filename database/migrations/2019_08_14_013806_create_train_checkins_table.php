<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTrainCheckinsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('train_checkins', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('status_id')
                ->references('id')->on('statuses')->unique();
            $table->string('trip_id')
                ->references('trip_id')->on('hafas_trips');
            $table->integer('origin')
                ->references('ibnr')->on('train_stations');
            $table->integer('destination')
                ->references('ibnr')->on('train_stations');
            $table->integer('distance');
            $table->timestampTz('departure');
            $table->timestampTz('arrival')->nullable();
            $table->integer('points')->nullable();
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
        Schema::dropIfExists('train_checkins');
    }
}
