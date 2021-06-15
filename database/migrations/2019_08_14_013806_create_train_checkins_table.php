<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTrainCheckinsTable extends Migration
{

    public function up(): void {
        Schema::create('train_checkins', function(Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('status_id')->unique();
            $table->string('trip_id');
            $table->unsignedBigInteger('origin');
            $table->unsignedBigInteger('destination');
            $table->integer('distance');
            $table->timestampTz('departure')->comment('planned departure');
            $table->timestampTz('arrival')->nullable()->comment('planned arrival');
            $table->integer('points')->nullable();
            $table->integer('delay')->nullable();
            $table->timestamps();

            $table->foreign('status_id')
                  ->references('id')
                  ->on('statuses')
                  ->cascadeOnDelete();
            $table->foreign('origin')
                  ->references('ibnr')//TODO: Should use 'id' instead...
                  ->on('train_stations');
            $table->foreign('destination')
                  ->references('ibnr')//TODO: Should use 'id' instead...
                  ->on('train_stations');
            $table->foreign('trip_id')
                  ->references('trip_id')//TODO: Should use 'id' instead...
                  ->on('hafas_trips');
        });
    }

    public function down(): void {
        Schema::dropIfExists('train_checkins');
    }
}
