<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class() extends Migration
{
    public function up(): void
    {
        DB::table('hafas_operators')->orderBy('id')->each(function (object $row): void {
            DB::table('operators')->insert([
                'id' => Str::uuid()->toString(),
                'legacy_id' => $row->id,
                'name' => $row->name,
                'created_at' => $row->created_at,
                'updated_at' => $row->updated_at,
            ]);
        });

        // Make legacy_id auto-increment so new operators get sequential IDs (MySQL only).
        if (in_array(DB::connection()->getDriverName(), ['mysql', 'mariadb'])) {
            DB::statement('ALTER TABLE operators MODIFY COLUMN legacy_id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT');
        }
    }

    public function down(): void
    {
        DB::table('operators')->truncate();

        if (in_array(DB::connection()->getDriverName(), ['mysql', 'mariadb'])) {
            DB::statement('ALTER TABLE operators MODIFY COLUMN legacy_id BIGINT UNSIGNED NULL');
        }
    }
};
