<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('station_identifiers', function (Blueprint $table) {
            $table->index(['type', 'identifier']);
        });
    }

    public function down(): void
    {
        Schema::table('station_identifiers', function (Blueprint $table) {
            $table->dropIndex('station_identifiers_type_identifier_index');
        });
    }
};
