<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEventsTable extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::create('events', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->string('name');
            $table->string('slug');
            $table->string('hashtag');
            $table->string('host'); // Einfach nur Name des Veranstalters, weil einfacher I guess
            $table->string('url'); // Event-URL
            $table->integer('trainstation');
            $table->dateTime('begin');
            $table->dateTime('end');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::dropIfExists('events');
    }
}
