<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void {
        Schema::table('social_login_profiles', static function(Blueprint $table) {
            $table->text('twitter_refresh_token')->nullable();
            $table->timestamp('twitter_token_expires_at')->nullable();
        });
    }

    public function down(): void {
        Schema::table('social_login_profiles', static function(Blueprint $table) {
            $table->removeColumn('twitter_refresh_token');
            $table->removeColumn('twitter_token_expires_at');
        });
    }
};
