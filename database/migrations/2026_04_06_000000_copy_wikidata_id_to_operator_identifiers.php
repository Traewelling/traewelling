<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class() extends Migration
{
    public function up(): void
    {
        DB::table('hafas_operators')
            ->whereNotNull('wikidata_id')
            ->orderBy('id')
            ->each(function (object $row): void {
                DB::table('operator_identifiers')->insert([
                    'id' => Str::uuid()->toString(),
                    'operator_id' => $row->id,
                    'type' => 'wikidata',
                    'identifier' => $row->wikidata_id,
                    'source' => null,
                    'name' => null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            });
    }

    public function down(): void
    {
        DB::table('operator_identifiers')->where('type', 'wikidata')->delete();
    }
};
