<?php

use App\Models\Stopover;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{

    /**
     * This is required to fix issue#1010.
     * If no arrival/departure is set because (e.g.) this is an entry/exit only stopover,
     * there were problems while checking in and displaying the checkin afterwards.
     *
     * @url https://github.com/Traewelling/traewelling/issues/1010
     * @return void
     */
    public function up(): void {
        Stopover::whereNull('arrival_planned')
                     ->whereNotNull('departure_planned')
                     ->update(['arrival_planned' => DB::raw('departure_planned')]);


        Stopover::whereNull('departure_planned')
                     ->whereNotNull('arrival_planned')
                     ->update(['departure_planned' => DB::raw('arrival_planned')]);
    }
};
