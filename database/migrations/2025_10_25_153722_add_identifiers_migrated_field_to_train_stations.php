<?php

use App\Jobs\MigrationStationIdentifiers;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('train_stations', function (Blueprint $table) {
            $table->boolean('identifiers_migrated')->default(false);
            $table->index('identifiers_migrated');
        });

        MigrationStationIdentifiers::dispatch();
    }

    public function down(): void
    {
        Schema::table('train_stations', function (Blueprint $table) {
            $table->dropIndex('train_stations_identifiers_migrated_index');
            $table->dropColumn('identifiers_migrated');
        });
    }
};
