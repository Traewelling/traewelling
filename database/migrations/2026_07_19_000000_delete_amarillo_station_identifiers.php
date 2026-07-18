<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class() extends Migration
{
    public function up(): void
    {
        DB::table('station_identifiers')
            ->where('identifier', 'like', 'de-amarillo-bw\_%')
            ->delete();
    }
};
