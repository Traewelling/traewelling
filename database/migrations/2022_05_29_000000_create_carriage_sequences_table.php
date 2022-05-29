<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up(): void {
        Schema::create('carriage_sequences', static function(Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('stopover_id');
            $table->unsignedTinyInteger('position');
            $table->string('sequence');
            $table->string('vehicle_type');
            $table->string('vehicle_number');
            $table->unsignedInteger('order_number');
            $table->timestamps();

            $table->unique(['stopover_id', 'position']);
        });
    }

    public function down(): void {
        Schema::dropIfExists('carriage_sequences');
    }
};
