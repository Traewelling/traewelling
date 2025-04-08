<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void {
        Schema::table('social_login_profiles', function(Blueprint $table) {
            $table->string('twitter_id')->nullable()->change();
        });

        Schema::table('social_login_profiles', function(Blueprint $table) {
            $table->dropForeign(['mastodon_server']);

            $table->unsignedBigInteger('mastodon_server')->nullable()->change();
        });

        Schema::table('social_login_profiles', function(Blueprint $table) {
            $table->foreign('mastodon_server')->references('id')->on('mastodon_servers');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        Schema::table('social_login_profiles', function(Blueprint $table) {
            $table->string('twitter_id')->nullable(false)->change();
        });

        Schema::table('social_login_profiles', function(Blueprint $table) {
            $table->dropForeign(['mastodon_server']);

            $table->unsignedBigInteger('mastodon_server')->nullable(false)->change();
        });

        Schema::table('social_login_profiles', function(Blueprint $table) {
            $table->foreign('mastodon_server')->references('id')->on('mastodon_servers');
        });
    }
};
