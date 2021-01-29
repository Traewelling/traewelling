<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTrainStationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::create('train_stations', function(Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('ibnr')->unique();
            $table->string('name');
            $table->decimal('latitude', 8, 6);
            $table->decimal('longitude', 8, 6);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::dropIfExists('train_stations');
    }
}
