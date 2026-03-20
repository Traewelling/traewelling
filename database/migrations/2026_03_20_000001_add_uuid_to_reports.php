<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::table('reports', static function (Blueprint $table) {
            $table->uuid('uuid')->nullable();
        });

        // Fill UUIDs for all existing rows
        DB::table('reports')->get()->each(static function (object $report): void {
            DB::table('reports')
                ->where('id', $report->id)
                ->update(['uuid' => Str::uuid()->toString()]);
        });

        Schema::table('reports', static function (Blueprint $table) {
            $table->uuid('uuid')->nullable(false)->change();
        });
    }

    public function down(): void
    {
        Schema::table('reports', static function (Blueprint $table) {
            $table->dropColumn('uuid');
        });
    }
};
