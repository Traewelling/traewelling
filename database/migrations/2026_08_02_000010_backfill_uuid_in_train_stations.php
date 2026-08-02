<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class() extends Migration
{
    private const CHUNK_SIZE = 1_000;

    public function up(): void
    {
        if (in_array(DB::getDriverName(), ['mysql', 'mariadb'], true)) {
            DB::statement('UPDATE train_stations SET uuid = UUID()');

            return;
        }

        DB::table('train_stations')
            ->select('id')
            ->orderBy('id')
            ->chunkById(self::CHUNK_SIZE, function (iterable $rows): void {
                foreach ($rows as $row) {
                    DB::table('train_stations')
                        ->where('id', $row->id)
                        ->update(['uuid' => Str::uuid()->toString()]);
                }
            });
    }
};
