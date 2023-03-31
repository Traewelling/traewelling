<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up(): void {
        Schema::table('users', static function(Blueprint $table) {
            $table->boolean('shadow_banned')
                  ->default(false)
                  ->after('language')
                  ->index();
        });
    }

    public function down(): void {
        Schema::table('users', static function(Blueprint $table) {
            $table->dropColumn(['shadow_banned']);
        });
    }
};
