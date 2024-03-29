<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddUniqueKeyToPolyLines extends Migration
{
    public function up(): void {
        Schema::table('poly_lines', function(Blueprint $table) {
            $table->unique(['hash']);
        });
    }


    public function down(): void {
        Schema::table('poly_lines', function(Blueprint $table) {
            $table->dropUnique(['hash']);
        });
    }
}
