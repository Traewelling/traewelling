<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void {
        Schema::create('trusted_users', static function(Blueprint $table) {
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('trusted_id')->constrained('users')->onDelete('cascade');
            $table->timestamps();

            $table->primary(['user_id', 'trusted_id']);

            $table->comment('This table is used to store trusted users for friend checkin.');
        });
    }

    public function down(): void {
        Schema::dropIfExists('trusted_users');
    }
};
