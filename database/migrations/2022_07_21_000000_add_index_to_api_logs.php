<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up(): void {
        Schema::table('api_logs', static function(Blueprint $table) {
            $table->index(['user_agent_id', 'created_at']);

            $table->index(['method', 'route', 'created_at']);
            $table->dropIndex(['created_at', 'method', 'route']);
        });
    }

    public function down(): void {
        Schema::table('api_logs', static function(Blueprint $table) {
            $table->dropIndex(['user_agent_id', 'created_at']);

            $table->index(['created_at', 'method', 'route']);
            $table->dropIndex(['method', 'route', 'created_at']);
        });
    }
};
