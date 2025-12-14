<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void {
        Schema::create('year_in_review_cache', function(Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->unsignedInteger('year');
            $table->json('data');
            $table->timestamps();

            $table->unique(['user_id', 'year']);
            $table->index('updated_at');
        });
    }

    public function down(): void {
        Schema::dropIfExists('year_in_review_cache');
    }
};
