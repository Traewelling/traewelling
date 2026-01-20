<?php

use App\Enum\MastodonVisibility;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('social_login_profiles', static function (Blueprint $table) {
            $table->unsignedTinyInteger('mastodon_visibility')
                ->default(MastodonVisibility::UNLISTED->value)
                ->after('mastodon_token');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('social_login_profiles', static function (Blueprint $table) {
            $table->dropColumn('mastodon_visibility');
        });
    }
};
