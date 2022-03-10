<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up(): void {
        Schema::table('users', static function(Blueprint $table) {
            $table->unsignedInteger('privacy_hide_days')
                  ->comment('Set statuses private after x days')
                  ->nullable()->default(null)
                  ->after('prevent_index');
        });
    }

    public function down(): void {
        Schema::table('users', static function(Blueprint $table) {
            $table->dropColumn(['privacy_hide_days']);
        });
    }
};
