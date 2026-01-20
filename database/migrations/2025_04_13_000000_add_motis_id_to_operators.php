<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::table('hafas_operators', function (Blueprint $table) {
            $table->unsignedBigInteger('motis_id')->nullable()->unique()->after('name');
            $table->string('motis_source')->nullable()->after('motis_id');
        });
    }

    public function down(): void
    {
        Schema::table('hafas_operators', function (Blueprint $table) {
            $table->dropColumn('motis_id');
            $table->dropColumn('motis_source');
        });
    }
};
