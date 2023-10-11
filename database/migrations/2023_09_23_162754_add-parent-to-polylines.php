<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void {
        if (!Schema::hasColumn('poly_lines', 'parent_id') {
            Schema::table('poly_lines', function(Blueprint $table) {
                $table->unsignedBigInteger('parent_id')->nullable()->after('id');
                $table->foreign('parent_id')->references('id')->on('poly_lines');
            });
        }
    }

    public function down(): void {
        try {
            Schema::table('poly_lines', function(Blueprint $table) {
                $table->dropForeign(['parent_id']);
                $table->dropColumn('parent_id');
            });
        } catch(\Throwable) {
            Schema::table('poly_lines, function(Blueprint $table) {
                $table->dropColumn('parent_id);
            });
        }
    }
};
