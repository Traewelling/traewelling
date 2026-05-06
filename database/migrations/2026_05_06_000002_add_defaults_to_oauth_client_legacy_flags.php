<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::table('oauth_clients', static function (Blueprint $table): void {
            $table->boolean('personal_access_client')->default(false)->change();
            $table->boolean('password_client')->default(false)->change();
        });
    }

    public function down(): void
    {
        Schema::table('oauth_clients', static function (Blueprint $table): void {
            $table->boolean('personal_access_client')->default(null)->change();
            $table->boolean('password_client')->default(null)->change();
        });
    }
};
