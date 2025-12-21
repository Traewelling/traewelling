<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('status_hidden_users', function (Blueprint $table) {
            $table->id();
            $table->foreignId('status_id')->constrained('statuses')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['status_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('status_hidden_users');
    }
};
