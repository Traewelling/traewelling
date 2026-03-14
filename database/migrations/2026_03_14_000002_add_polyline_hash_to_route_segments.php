<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

// On MySQL/MariaDB: ALGORITHM=INPLACE, LOCK=NONE for a non-blocking DDL.
// Adding a nullable column without default uses INSTANT on MySQL 8.0+ / MariaDB 10.3+,
// falling back to INPLACE – no table rebuild, no read/write lock.
// On SQLite (test env): falls back to standard Schema builder.
return new class() extends Migration
{
    public function up(): void
    {
        if (in_array(DB::getDriverName(), ['mysql', 'mariadb'], true)) {
            DB::statement(
                'ALTER TABLE route_segments
                 ADD COLUMN polyline_hash VARCHAR(32) NULL AFTER polyline_precision,
                 ALGORITHM=INPLACE, LOCK=NONE'
            );

            return;
        }

        Schema::table('route_segments', function (Blueprint $table): void {
            $table->string('polyline_hash', 32)->nullable()->after('polyline_precision');
        });
    }

    public function down(): void
    {
        Schema::table('route_segments', function (Blueprint $table): void {
            $table->dropColumn('polyline_hash');
        });
    }
};
