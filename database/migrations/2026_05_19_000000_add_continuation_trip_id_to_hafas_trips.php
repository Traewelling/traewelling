<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

// On MySQL/MariaDB: column added with ALGORITHM=INPLACE, LOCK=NONE (no table rebuild, no read/write lock).
// FK added separately with foreign_key_checks=0 so MariaDB skips the full-table validation scan.
// On SQLite (test env): falls back to standard Schema builder.
return new class() extends Migration
{
    public function up(): void
    {
        if (in_array(DB::getDriverName(), ['mysql', 'mariadb'], true)) {
            DB::statement('ALTER TABLE hafas_trips ADD COLUMN continuation_trip_id BIGINT UNSIGNED NULL, ALGORITHM=INPLACE, LOCK=NONE');
            DB::statement('SET foreign_key_checks=0');
            DB::statement('ALTER TABLE hafas_trips ADD CONSTRAINT hafas_trips_continuation_trip_id_foreign FOREIGN KEY (continuation_trip_id) REFERENCES hafas_trips (id) ON DELETE SET NULL');
            DB::statement('SET foreign_key_checks=1');

            return;
        }

        Schema::table('hafas_trips', function (Blueprint $table): void {
            $table->unsignedBigInteger('continuation_trip_id')->nullable();
            $table->foreign('continuation_trip_id')->references('id')->on('hafas_trips')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('hafas_trips', function (Blueprint $table): void {
            $table->dropForeign(['continuation_trip_id']);
            $table->dropColumn('continuation_trip_id');
        });
    }
};
