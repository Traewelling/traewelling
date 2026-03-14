<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::table('oauth_access_tokens', function (Blueprint $table): void {
            // Fixes three Prometheus OAuth queries. Previously: full table scan + tmp table + filesort on 587K rows
            $table->index('client_id', 'oauth_access_tokens_client_id_index');
        });
    }

    public function down(): void
    {
        Schema::table('oauth_access_tokens', function (Blueprint $table): void {
            $table->dropIndex('oauth_access_tokens_client_id_index');
        });
    }
};
