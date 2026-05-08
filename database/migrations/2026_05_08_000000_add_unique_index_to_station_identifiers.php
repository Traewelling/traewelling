<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        // Remove duplicates, keeping the row with the lowest id per (type, identifier) pair.
        DB::statement('
            DELETE si FROM station_identifiers si
            INNER JOIN station_identifiers si2
                ON si.type = si2.type
                AND si.identifier = si2.identifier
                AND si.id > si2.id
        ');

        Schema::table('station_identifiers', function (Blueprint $table) {
            $table->unique(['type', 'identifier'], 'station_identifiers_type_identifier_unique');
        });
    }

    public function down(): void
    {
        Schema::table('station_identifiers', function (Blueprint $table) {
            $table->dropUnique('station_identifiers_type_identifier_unique');
        });
    }
};
