<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateHafasTripsTable extends Migration
{

    public function up(): void {
        Schema::create('hafas_trips', function(Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('trip_id')->unique();
            $table->string('category');
            $table->string('number');
            $table->string('linename');
            $table->unsignedBigInteger('origin');
            $table->unsignedBigInteger('destination');
            $table->json('stopovers')->nullable();
            $table->string('polyline')->nullable();
            $table->timestampTz('departure')->nullable();
            $table->timestampTz('arrival')->nullable();
            $table->integer('delay')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('hafas_trips');
    }
}
