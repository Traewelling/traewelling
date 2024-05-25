<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void {
        Schema::table('hafas_trips', static function(Blueprint $table) {
            $table->index(['operator_id', 'category']);
        });
    }

    public function down(): void {
        Schema::table('hafas_trips', static function(Blueprint $table) {
            $table->dropIndex(['operator_id', 'category']);
        });
    }
};
