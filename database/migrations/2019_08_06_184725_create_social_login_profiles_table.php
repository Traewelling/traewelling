<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSocialLoginProfilesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('social_login_profiles', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id')->unsigned();
            $table->string('twitter_id')->nullable();
            $table->text('twitter_token')->nullable();
            $table->text('twitter_tokenSecret')->nullable();
            $table->string('mastodon_id')->nullable();
            $table->integer('mastodon_server')
                ->nullable();
            $table->text('mastodon_token')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('social_login_profiles');
    }
}
