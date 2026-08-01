<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::table('mastodon_servers', function (Blueprint $table) {
            $table->string('scopes')->default('read write')->after('client_secret');
        });
    }

    public function down(): void
    {
        Schema::table('mastodon_servers', function (Blueprint $table) {
            $table->dropColumn('scopes');
        });
    }
};
