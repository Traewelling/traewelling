<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void {
        Schema::create('oauth_access_tokens', static function(Blueprint $table) {
            $table->string('id', 100)->primary();
            $table->integer('user_id')->nullable()->index();
            $table->integer('client_id');
            $table->string('name')->nullable();
            $table->text('scopes')->nullable();
            $table->boolean('revoked');
            $table->timestamps();
            $table->dateTime('expires_at')->nullable();
        });
    }

    public function down(): void {
        Schema::dropIfExists('oauth_access_tokens');
    }
};
