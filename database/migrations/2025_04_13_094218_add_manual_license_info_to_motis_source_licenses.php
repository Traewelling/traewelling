<?php

use App\Models\License;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::table('motis_source_licenses', function (Blueprint $table) {
            $table->foreignIdFor(License::class)->after('spdx')->nullable()->constrained();
            $table->boolean('force_active')->default(false)->after('active');
        });
    }

    public function down(): void
    {
        Schema::table('motis_source_licenses', function (Blueprint $table) {
            $table->dropForeign(['license_id']);
            $table->dropColumn('license_id');
            $table->dropColumn('force_active');
        });
    }
};
