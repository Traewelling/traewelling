<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::table('motis_source_licenses', function (Blueprint $table) {
            $table->string('human_name')->nullable()->after('name');
        });
    }

    public function down(): void
    {
        Schema::table('motis_source_licenses', function (Blueprint $table) {
            $table->dropColumn('human_name');
        });
    }
};
