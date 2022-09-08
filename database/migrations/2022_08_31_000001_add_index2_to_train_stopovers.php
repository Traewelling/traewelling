<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up(): void {
        Schema::table('train_stopovers', static function(Blueprint $table) {
            $table->index('departure_planned');
            $table->index('departure_real');
            $table->index('arrival_planned');
            $table->index('arrival_real');
        });
    }

    public function down(): void {
        Schema::table('train_stopovers', static function(Blueprint $table) {
            $table->dropIndex(['departure_planned']);
            $table->dropIndex(['departure_real']);
            $table->dropIndex(['arrival_planned']);
            $table->dropIndex(['arrival_real']);
        });
    }
};
