<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

// On MySQL/MariaDB: batched UPDATE with MD5() and LIMIT 500 keeps row locks short-lived.
// On SQLite (test env): MD5() and UPDATE...LIMIT are unavailable, so hashes are computed
// via PHP and applied row-by-row. Not performant, but test databases are tiny.
return new class() extends Migration
{
    private const BATCH_SIZE = 500;

    public function up(): void
    {
        if (in_array(DB::getDriverName(), ['mysql', 'mariadb'], true)) {
            do {
                $affected = DB::affectingStatement(
                    'UPDATE route_segments
                     SET polyline_hash = MD5(polyline)
                     WHERE polyline_hash IS NULL
                     LIMIT ' . self::BATCH_SIZE
                );
            } while ($affected > 0);

            return;
        }

        DB::table('route_segments')
            ->whereNull('polyline_hash')
            ->orderBy('id')
            ->chunkById(self::BATCH_SIZE, function (iterable $rows): void {
                foreach ($rows as $row) {
                    DB::table('route_segments')
                        ->where('id', $row->id)
                        ->update(['polyline_hash' => md5($row->polyline)]);
                }
            });
    }
};
