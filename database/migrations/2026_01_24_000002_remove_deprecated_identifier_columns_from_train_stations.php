<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Drop indexes first (required for SQLite compatibility)
        Schema::table('train_stations', function (Blueprint $table) {
            $table->dropUnique(['ibnr']);
            $table->dropIndex(['rilIdentifier']);
            $table->dropIndex(['identifiers_migrated']);
            $table->dropIndex('ifopt');
        });

        Schema::table('train_stations', function (Blueprint $table) {
            // Drop deprecated identifier columns
            // All identifiers are now stored in station_identifiers table
            $table->dropColumn([
                'ibnr',
                'rilIdentifier',
                'wikidata_id',
                'ifopt_a',
                'ifopt_b',
                'ifopt_c',
                'ifopt_d',
                'ifopt_e',
                'identifiers_migrated',
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('train_stations', function (Blueprint $table) {
            // Restore deprecated identifier columns
            $table->string('ibnr')->unique()->after('id');
            $table->string('rilIdentifier', 10)->nullable()->after('ibnr');
            $table->string('wikidata_id')->nullable()->after('ibnr');
            $table->string('ifopt_a')->nullable()->comment('Country')->after('wikidata_id');
            $table->unsignedInteger('ifopt_b')->nullable()->comment('Administrative Area')->after('wikidata_id');
            $table->unsignedInteger('ifopt_c')->nullable()->comment('Mode or Stop Place')->after('wikidata_id');
            $table->unsignedInteger('ifopt_d')->nullable()->comment('Stop Place or Stop Place Component')->after('wikidata_id');
            $table->unsignedInteger('ifopt_e')->nullable()->comment('Stop Place Component (or unused)')->after('wikidata_id');
            $table->boolean('identifiers_migrated')->default(false)->after('ifopt_e');

            // Add indexes
            $table->index(['rilIdentifier']);
            $table->index('identifiers_migrated');
            $table->index(['ifopt_a', 'ifopt_b', 'ifopt_c', 'ifopt_d', 'ifopt_e'], 'ifopt');
        });
    }
};
