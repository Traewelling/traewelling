<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEventsTable extends Migration
{

    public function up(): void {
        Schema::create('events', function(Blueprint $table) {
            $table->bigIncrements('id');

            $table->string('name');
            $table->string('slug')->unique();
            $table->string('hashtag');
            $table->string('host'); // Einfach nur Name des Veranstalters, weil einfacher I guess
            $table->string('url');  // Event-URL
            $table->unsignedBigInteger('trainstation');
            $table->dateTime('begin');
            $table->dateTime('end');

            $table->timestamps();

            $table->foreign('trainstation')
                  ->references('id')
                  ->on('train_stations')
                  ->cascadeOnUpdate();
        });
    }

    public function down(): void {
        Schema::dropIfExists('events');
    }
}
