<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class() extends Migration
{
    private const CHUNK_SIZE = 30_000;

    public function up(): void
    {
        if (in_array(DB::getDriverName(), ['mysql', 'mariadb'], true)) {
            $minId = (int) DB::table('train_stopovers')->min('id');
            $maxId = (int) DB::table('train_stopovers')->max('id');

            for ($start = $minId; $start <= $maxId; $start += self::CHUNK_SIZE) {
                DB::affectingStatement(
                    'UPDATE train_stopovers SET uuid = UUID() WHERE id >= ? AND id < ? AND uuid IS NULL',
                    [$start, $start + self::CHUNK_SIZE]
                );
            }

            return;
        }

        DB::table('train_stopovers')
            ->select('id')
            ->orderBy('id')
            ->chunkById(self::CHUNK_SIZE, function (iterable $rows): void {
                foreach ($rows as $row) {
                    DB::table('train_stopovers')
                        ->where('id', $row->id)
                        ->update(['uuid' => Str::uuid()->toString()]);
                }
            });
    }
};
