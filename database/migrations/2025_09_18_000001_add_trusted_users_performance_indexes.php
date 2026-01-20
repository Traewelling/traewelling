<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('trusted_users', function (Blueprint $table) {
            $table->index(['trusted_id', 'user_id', 'expires_at'], 'idx_trusted_users_lookup');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('trusted_users', function (Blueprint $table) {
            $table->dropIndex('idx_trusted_users_lookup');
        });
    }
};
