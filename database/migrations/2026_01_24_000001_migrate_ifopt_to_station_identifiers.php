<?php

use App\Jobs\MigrationStationIdentifiers;
use App\Models\Station;
use Illuminate\Database\Migrations\Migration;

return new class() extends Migration
{
    public function up(): void
    {
        // Reset migration flag to ensure all stations are processed again (including IFOPT)
        Station::query()->update(['identifiers_migrated' => false]);

        // Dispatch migration job in batches
        $totalStations = Station::where('identifiers_migrated', false)->count();
        $batches = ceil($totalStations / 1000);

        for ($i = 0; $i < $batches; $i++) {
            MigrationStationIdentifiers::dispatch();
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove IFOPT identifiers from station_identifiers table
        \App\Models\StationIdentifier::where('type', \App\StationIdentifierType::DE_DB_IFOPT)->delete();

        // Reset migration flag
        Station::query()->update(['identifiers_migrated' => false]);
    }
};
