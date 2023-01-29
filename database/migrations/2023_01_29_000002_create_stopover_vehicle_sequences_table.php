<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void {
        Schema::create('stopover_vehicle_sequences', static function(Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('stopover_id');
            $table->unsignedTinyInteger('position');
            $table->string('sequence');
            $table->unsignedBigInteger('vehicle_id');
            $table->unsignedInteger('order_number');
            $table->timestamps();

            $table->unique(['stopover_id', 'position']);

            $table->foreign('stopover_id')
                  ->references('id')
                  ->on('train_stopovers')
                  ->cascadeOnDelete();
            $table->foreign('vehicle_id')
                  ->references('id')
                  ->on('vehicles');
        });
    }

    public function down(): void {
        Schema::dropIfExists('stopover_vehicle_sequences');
    }
};
