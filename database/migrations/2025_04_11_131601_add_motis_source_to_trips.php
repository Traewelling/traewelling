<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::table('hafas_trips', function (Blueprint $table) {
            $table->uuid('motis_source_license_id')->after('motis_source')->nullable();
            $table->foreign('motis_source_license_id', 'motis_source_license_id_foreign')
                ->references('id')->on('motis_source_licenses')
                ->onDelete('set null')
                ->onUpdate('cascade');
        });
    }

    public function down(): void
    {
        Schema::table('hafas_trips', function (Blueprint $table) {
            $table->dropForeign('motis_source_license_id_foreign');
            $table->dropColumn('motis_source_license_id');
        });
    }
};
