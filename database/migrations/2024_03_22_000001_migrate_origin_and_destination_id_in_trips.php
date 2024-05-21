<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void {
        DB::table('hafas_trips')->update([
                                             'origin_id'      => DB::raw('(SELECT id FROM train_stations WHERE ibnr = hafas_trips.origin)'),
                                             'destination_id' => DB::raw('(SELECT id FROM train_stations WHERE ibnr = hafas_trips.destination)'),
                                         ]);
    }
};
