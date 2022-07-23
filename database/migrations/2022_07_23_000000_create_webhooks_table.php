<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up(): void {
        Schema::create('webhooks', static function(Blueprint $table) {
            $table->id();
            $table->uuid('external_id')->unique();
            $table->string('access_token_id', 100);
            $table->unsignedBigInteger('user_id');
            $table->string('url');
            $table->timestamps();

            $table->foreign('user_id')
                  ->references('id')
                  ->on('users')
                  ->cascadeOnDelete();
            $table->foreign('access_token_id')
                  ->references('id')
                  ->on('access_tokens')
                  ->cascadeOnDelete();
        });
    }

    public function down(): void {
        Schema::dropIfExists('webhooks');
    }
};
