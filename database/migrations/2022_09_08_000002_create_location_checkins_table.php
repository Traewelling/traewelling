<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void {
        Schema::create('location_checkins', static function(Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('status_id');
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('location_id');

            $table->timestampTz('arrival')->nullable();
            $table->timestampTz('departure')->nullable();

            //$table->unsignedInteger('points'); //TODO: Move points from location_checkins and train_checkins to status.
            $table->timestamps();

            $table->foreign('status_id')->references('id')->on('statuses')->cascadeOnDelete();
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->foreign('location_id')->references('id')->on('locations');
        });
    }

    public function down(): void {
        Schema::dropIfExists('location_checkins');
    }
};
