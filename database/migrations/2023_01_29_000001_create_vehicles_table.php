<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void {
        Schema::create('vehicles', static function(Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->unsignedBigInteger('vehicle_group_id')->nullable();
            $table->string('classification')->index();
            $table->timestamps();

            $table->foreign('vehicle_group_id')->references('id')->on('vehicle_groups');
        });
    }

    public function down(): void {
        Schema::dropIfExists('vehicles');
    }
};
