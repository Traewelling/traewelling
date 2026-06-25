<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::table('oauth_access_tokens', static function (Blueprint $table) {
            $table->index(['client_id', 'revoked', 'user_id'], 'oat_client_revoked_user_idx');
            $table->index(['revoked', 'expires_at', 'client_id', 'user_id'], 'oat_revoked_expires_client_user_idx');
        });
    }

    public function down(): void
    {
        Schema::table('oauth_access_tokens', static function (Blueprint $table) {
            $table->dropIndex('oat_client_revoked_user_idx');
            $table->dropIndex('oat_revoked_expires_client_user_idx');
        });
    }
};
