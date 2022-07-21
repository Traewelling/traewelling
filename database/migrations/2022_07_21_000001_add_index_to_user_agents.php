<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up(): void {
        Schema::table('user_agents', static function(Blueprint $table) {
            $table->index(['id', 'user_agent']);
        });
    }

    public function down(): void {
        Schema::table('user_agents', static function(Blueprint $table) {
            $table->dropIndex(['id', 'user_agent']);
        });
    }
};
