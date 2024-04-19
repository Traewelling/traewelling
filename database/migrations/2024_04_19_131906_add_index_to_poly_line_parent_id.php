<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void {
        Schema::table('poly_lines', static function (Blueprint $table) {
            $table->index('parent_id');
        });
    }

    public function down(): void {
        Schema::table('poly_lines', static function (Blueprint $table) {
            $table->dropIndex(['parent_id']);
        });
    }
};
