<?php

use App\Enum\MastodonVisibility;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('social_login_profiles', function (Blueprint $table) {
            $table->unsignedTinyInteger('mastodon_visibility')
                  ->default(MastodonVisibility::UNLISTED->value)
                  ->after('mastodon_token');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('social_login_profiles', function (Blueprint $table) {
            $table->dropColumn('mastodon_visibility');
        });
    }
};
