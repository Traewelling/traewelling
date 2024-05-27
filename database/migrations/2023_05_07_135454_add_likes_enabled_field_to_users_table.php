<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void {
        Schema::table('users', static function(Blueprint $table) {
            $table->boolean('likes_enabled')
                  ->default(true)
                  ->after('language');
        });
    }

    public function down(): void {
        Schema::table('users', static function(Blueprint $table) {
            $table->dropColumn('likes_enabled');
        });
    }
};
