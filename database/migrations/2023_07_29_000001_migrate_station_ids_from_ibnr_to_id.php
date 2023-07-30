<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void {
        if (App::runningUnitTests()) {
            //we don't need to migrate data in unit tests - also it would fail because this query is not allowed in sqlite
            return;
        }
        //migrate data
        DB::table('train_checkins')
          ->join('train_stations as tsOrigin', 'tsOrigin.ibnr', '=', 'train_checkins.origin')
          ->join('train_stations as tsDestination', 'tsDestination.ibnr', '=', 'train_checkins.destination')
          ->update([
                       'train_checkins.origin_id'      => DB::raw('tsOrigin.id'),
                       'train_checkins.destination_id' => DB::raw('tsDestination.id'),
                   ]);
    }

    public function down(): void {
        if (App::runningUnitTests()) {
            //we don't need to migrate data in unit tests - also it would fail because this query is not allowed in sqlite
            return;
        }
        //migrate data
        DB::table('train_checkins')
          ->join('train_stations as tsOrigin', 'tsOrigin.id', '=', 'train_checkins.origin_id')
          ->join('train_stations as tsDestination', 'tsDestination.id', '=', 'train_checkins.destination_id')
          ->update([
                       'train_checkins.origin'      => DB::raw('tsOrigin.ibnr'),
                       'train_checkins.destination' => DB::raw('tsDestination.ibnr'),
                   ]);
    }
};
