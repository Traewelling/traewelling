<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void {
        Schema::table('statuses', static function(Blueprint $table) {
            $table->index(['user_id', 'mastodon_post_id', 'created_at']);
        });
    }

    public function down(): void {
        Schema::table('statuses', static function(Blueprint $table) {
            $table->dropIndex(['user_id', 'mastodon_post_id', 'created_at']);
        });
    }
};
