<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('poly_lines', function (Blueprint $table) {
            $table->enum('source', ['hafas', 'brouter', 'upload'])
                ->default('hafas')
                ->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('poly_lines', function (Blueprint $table) {
            $table->enum('source', ['hafas', 'brouter'])
                ->default('hafas')
                ->change();
        });
    }
};
