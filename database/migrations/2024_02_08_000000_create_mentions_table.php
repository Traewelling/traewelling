<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void {
        Schema::create('mentions', static function(Blueprint $table) {
            $table->id();
            $table->foreignId('status_id')->constrained()->cascadeOnDelete();
            $table->foreignId('mentioned_id')->constrained('users')->cascadeOnDelete();
            $table->unsignedSmallInteger('position');
            $table->unsignedSmallInteger('length');
            $table->timestamps();
            $table->unique(['status_id', 'mentioned_id', 'position']);
        });
    }

    public function down(): void {
        Schema::dropIfExists('mentions');
    }
};
