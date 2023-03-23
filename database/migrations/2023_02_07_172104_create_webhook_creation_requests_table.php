<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up() {
        Schema::create('webhook_creation_requests', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->constrained()->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('oauth_client_id')->constrained()->cascadeOnUpdate()->cascadeOnDelete();
            $table->boolean('revoked')->default(false);
            $table->dateTime('expires_at');
            $table->string('events');
            $table->text('url');
        });
    }
    public function down() {
        Schema::dropIfExists('webhook_creation_requests');
    }
};
