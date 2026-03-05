<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class() extends Migration
{
    public function up(): void
    {
        DB::table('train_stations')
            ->whereNotNull('ifopt_a')
            ->chunkById(500, function ($stations): void {
                foreach ($stations as $station) {
                    $ifopt = $station->ifopt_a;
                    foreach (['ifopt_b', 'ifopt_c', 'ifopt_d', 'ifopt_e'] as $col) {
                        if ($station->$col !== null) {
                            $ifopt .= ':' . $station->$col;
                        }
                    }

                    $alreadyExists = DB::table('station_identifiers')
                        ->where('station_id', $station->id)
                        ->where('type', 'ifopt')
                        ->where('identifier', $ifopt)
                        ->exists();

                    if (!$alreadyExists) {
                        DB::table('station_identifiers')->insert([
                            'id' => (string) Str::uuid(),
                            'station_id' => $station->id,
                            'type' => 'ifopt',
                            'origin' => null,
                            'identifier' => $ifopt,
                            'name' => $station->name,
                            'relevance' => 0,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    }

                    DB::table('train_stations')
                        ->where('id', $station->id)
                        ->update([
                            'ifopt_a' => null,
                            'ifopt_b' => null,
                            'ifopt_c' => null,
                            'ifopt_d' => null,
                            'ifopt_e' => null,
                        ]);
                }
            });
    }
};
