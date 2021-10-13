<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class MakeTwitterIdUniqueInSocialLoginProfiles extends Migration
{

    public function up(): void {
        Schema::table('social_login_profiles', function(Blueprint $table) {
            $table->unique(['twitter_id']);
        });
    }

    public function down(): void {
        Schema::table('social_login_profiles', function(Blueprint $table) {
            $table->dropUnique(['twitter_id']);
        });
    }
}
