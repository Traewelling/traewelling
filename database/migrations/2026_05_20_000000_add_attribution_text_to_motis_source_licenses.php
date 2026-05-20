<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::table('motis_source_licenses', function (Blueprint $table) {
            $table->text('attribution_text')->nullable()->after('human_name');
        });
    }

    public function down(): void
    {
        Schema::table('motis_source_licenses', function (Blueprint $table) {
            $table->dropColumn('attribution_text');
        });
    }
};
