<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void {
        Schema::dropIfExists('api_logs');
    }

    public function down(): void {
        Schema::create('api_logs', static function(Blueprint $table) {
            $table->id();
            $table->string('method');
            $table->string('route');
            $table->unsignedTinyInteger('status_code')->nullable();
            $table->unsignedBigInteger('user_agent_id');
            $table->timestamps();

            $table->index(['created_at', 'method', 'route']);

            $table->foreign('user_agent_id')->references('id')->on('user_agents');
        });
    }
};
