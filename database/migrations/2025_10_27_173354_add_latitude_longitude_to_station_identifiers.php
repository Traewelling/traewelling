<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::table('station_identifiers', function (Blueprint $table) {
            $table->float('latitude')->nullable()->after('name');
            $table->float('longitude')->nullable()->after('latitude');
        });
    }

    public function down(): void
    {
        Schema::table('station_identifiers', function (Blueprint $table) {
            $table->dropColumn('latitude');
            $table->dropColumn('longitude');
        });
    }
};
