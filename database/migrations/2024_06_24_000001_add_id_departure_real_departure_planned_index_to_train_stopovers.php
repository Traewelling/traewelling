<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void {
        Schema::table('train_stopovers', static function(Blueprint $table) {
            $table->index(['id', 'departure_real', 'departure_planned']);
        });
    }

    public function down(): void {
        Schema::table('train_stopovers', static function(Blueprint $table) {
            $table->dropIndex(['id', 'departure_real', 'departure_planned']);
        });
    }
};
