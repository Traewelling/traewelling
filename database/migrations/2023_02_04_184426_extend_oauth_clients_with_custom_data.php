<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::table('oauth_clients', function (Blueprint $table) {
            $table->string('privacy_policy_url')->nullable();
            $table->boolean('webhooks_enabled')->default(false);
            $table->string('authorized_webhook_url')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::table('oauth_clients', function (Blueprint $table) {
            $table->dropColumn('privacy_policy_url');
            $table->dropColumn('webhooks_enabled');
            $table->dropColumn('authorized_webhook_url');
        });
    }
};
