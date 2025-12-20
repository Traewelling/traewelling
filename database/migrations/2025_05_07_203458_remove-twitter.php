<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void {
        if (Schema::connection(null)->getConnection()->getDriverName() === 'sqlite') {
            return;
        }

        Schema::table('social_login_profiles', function(Blueprint $table) {
            $table->dropUnique(['twitter_id']);
            $table->dropColumn('twitter_id');
        });
    }

    public function down(): void {
        Schema::table('social_login_profiles', function(Blueprint $table) {
            $table->string('twitter_id')->after('user_id')->nullable();
            $table->unique(['twitter_id']);
        });
    }
};
