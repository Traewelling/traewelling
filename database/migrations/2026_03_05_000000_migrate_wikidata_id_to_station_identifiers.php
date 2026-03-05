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
            ->whereNotNull('wikidata_id')
            ->chunkById(500, function ($stations): void {
                foreach ($stations as $station) {
                    $alreadyExists = DB::table('station_identifiers')
                        ->where('station_id', $station->id)
                        ->where('type', 'wikidata_id')
                        ->exists();

                    if (!$alreadyExists) {
                        DB::table('station_identifiers')->insert([
                            'id' => (string) Str::uuid(),
                            'station_id' => $station->id,
                            'type' => 'wikidata_id',
                            'origin' => null,
                            'identifier' => $station->wikidata_id,
                            'name' => $station->name,
                            'relevance' => 0,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    }

                    DB::table('train_stations')
                        ->where('id', $station->id)
                        ->update(['wikidata_id' => null]);
                }
            });
    }
};
