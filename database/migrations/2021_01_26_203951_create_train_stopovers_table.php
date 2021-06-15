<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTrainStopoversTable extends Migration
{
    public function up(): void {
        Schema::create('train_stopovers', function(Blueprint $table) {
            $table->id();

            $table->string('trip_id');
            $table->unsignedBigInteger('train_station_id');

            $table->timestamp('arrival_planned')->nullable();
            $table->timestamp('arrival_real')->nullable();

            $table->string('arrival_platform_planned')->nullable();
            $table->string('arrival_platform_real')->nullable();

            $table->timestamp('departure_planned')->nullable();
            $table->timestamp('departure_real')->nullable();

            $table->string('departure_platform_planned')->nullable();
            $table->string('departure_platform_real')->nullable();

            $table->timestamps();

            $table->unique(['trip_id', 'train_station_id']);

            $table->foreign('trip_id')
                  ->references('trip_id')
                  ->on('hafas_trips');
            $table->foreign('train_station_id')
                  ->references('id')
                  ->on('train_stations')
                  ->cascadeOnUpdate();
        });
    }

    public function down(): void {
        Schema::dropIfExists('train_stopovers');
    }
}
