<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void {
        Schema::dropColumns('social_login_profiles', [
            'twitter_token',
            'twitter_tokenSecret',
            'twitter_refresh_token',
            'twitter_token_expires_at',
        ]);
    }
};
