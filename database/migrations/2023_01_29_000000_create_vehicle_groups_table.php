<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void {
        Schema::create('vehicle_groups', static function(Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->enum('type', ['train', 'airplane', 'other'])->default('train'); //maybe useful for future implementations
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('vehicle_groups');
    }
};
