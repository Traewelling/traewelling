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
            //This has been changed from "json" to "string" so that the upcoming migrations won't fail b/c of non-matching collations.
            // We know it's bad practice but it's better than defining a collation in another migration. That Database-Shit. Not Code-Shit.
            $table->string('polyline')->nullable();
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
