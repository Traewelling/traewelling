<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void {
        Schema::table('train_checkins', static function(Blueprint $table) {
            $table->index(['status_id', 'departure']);
        });
    }

    public function down(): void {
        Schema::table('train_checkins', static function(Blueprint $table) {
            $table->dropIndex(['status_id', 'departure']);
        });
    }
};
