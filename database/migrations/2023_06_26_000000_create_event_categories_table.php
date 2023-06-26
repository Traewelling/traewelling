<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void {
        Schema::create('event_categories', static function(Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('event_id');
            $table->unsignedInteger('category');

            $table->timestamps();

            $table->foreign('event_id')
                  ->references('id')
                  ->on('events')
                  ->cascadeOnDelete();
            $table->unique(['event_id', 'category']);
        });
    }

    public function down(): void {
        Schema::dropIfExists('event_categories');
    }
};
