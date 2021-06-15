<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSocialLoginProfilesTable extends Migration
{

    public function up(): void {
        Schema::create('social_login_profiles', function(Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('twitter_id')->nullable();
            $table->text('twitter_token')->nullable();
            $table->text('twitter_tokenSecret')->nullable();
            $table->string('mastodon_id')->nullable();
            $table->integer('mastodon_server')
                  ->nullable();
            $table->text('mastodon_token')->nullable();
            $table->timestamps();

            $table->foreign('user_id')
                  ->references('id')
                  ->on('users')
                  ->onDelete('cascade')
                  ->onUpdate('cascade');
        });
    }

    public function down(): void {
        Schema::dropIfExists('social_login_profiles');
    }
}
