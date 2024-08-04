<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void {
        Schema::table('follows', static function(Blueprint $table) {
            $table->index(['follow_id', 'user_id']);
        });
    }

    public function down(): void {
        Schema::table('follows', static function(Blueprint $table) {
            $table->dropIndex(['follow_id', 'user_id']);
        });
    }
};
