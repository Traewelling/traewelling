<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::table('poly_lines', function (Blueprint $table) {
            // Before this change, the 'source' column was defined as an enum with values 'hafas' and 'brouter'.
            // While adding more sources, it is better to use a string column, to avoid future headaches.
            $table->string('source')->nullable()->default(null)->change();
        });
    }

    public function down(): void
    {
        DB::table('poly_lines')->whereNotIn('source', ['hafas', 'brouter'])->update(['source' => 'hafas']);

        Schema::table('poly_lines', function (Blueprint $table) {
            $table->enum('source', ['hafas', 'brouter'])->nullable(false)->default('hafas')->change();
        });
    }
};
