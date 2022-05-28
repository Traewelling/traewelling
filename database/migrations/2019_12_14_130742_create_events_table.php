<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEventsTable extends Migration
{

    public function up(): void {
        Schema::create('events', static function(Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug');
            $table->string('hashtag');
            $table->string('host');
            $table->string('url');
            $table->unsignedBigInteger('station_id');
            $table->dateTime('begin');
            $table->dateTime('end');
            $table->timestamps();

            $table->foreign('station_id')->references('id')->on('train_stations');
        });
    }

    public function down(): void {
        Schema::dropIfExists('events');
    }
}
