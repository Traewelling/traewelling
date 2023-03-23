<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void {
        Schema::table('train_checkins', static function(Blueprint $table) {
            $table->dropIndex(['departure', 'arrival']);
            $table->index(['departure', 'arrival', 'status_id']);
        });
    }

    public function down(): void {
        Schema::table('train_checkins', static function(Blueprint $table) {
            $table->dropIndex(['departure', 'arrival', 'status_id']);
            $table->index(['departure', 'arrival']);
        });
    }
};
