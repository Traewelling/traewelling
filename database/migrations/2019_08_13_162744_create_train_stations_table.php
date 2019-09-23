<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTrainStationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('train_stations', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('ibnr')->unique();
            $table->string('name');
            $table->decimal('latitude');
            $table->decimal('longitude');
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
        Schema::dropIfExists('train_stations');
    }
}
