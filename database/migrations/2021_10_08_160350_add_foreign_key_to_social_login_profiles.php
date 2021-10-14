<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddForeignKeyToSocialLoginProfiles extends Migration
{

    public function up(): void {
        Schema::table('social_login_profiles', function(Blueprint $table) {
            $table->unsignedBigInteger('mastodon_server')->change();

            $table->foreign('mastodon_server')
                  ->references('id')
                  ->on('mastodon_servers');
        });
    }

    public function down(): void {
        Schema::table('social_login_profiles', function(Blueprint $table) {
            $table->integer('mastodon_server')->change();

            $table->dropForeign(['mastodon_server']);
        });
    }
}
