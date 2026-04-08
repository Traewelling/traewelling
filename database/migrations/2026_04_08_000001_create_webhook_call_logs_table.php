<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::create('webhook_call_logs', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignId('webhook_id')->nullable()->constrained('webhooks')->nullOnDelete();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('oauth_client_id');
            $table->string('event');
            $table->string('url');
            $table->tinyInteger('attempt');
            $table->smallInteger('response_code')->nullable()->comment('null = connection error or timeout');
            $table->timestamp('created_at');

            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('webhook_call_logs');
    }
};
