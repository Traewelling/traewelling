<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::table('social_login_profiles', function (Blueprint $table): void {
            $table->string('mastodon_username')->nullable()->after('mastodon_id');
        });
    }

    public function down(): void
    {
        Schema::table('social_login_profiles', function (Blueprint $table): void {
            $table->dropColumn('mastodon_username');
        });
    }
};
