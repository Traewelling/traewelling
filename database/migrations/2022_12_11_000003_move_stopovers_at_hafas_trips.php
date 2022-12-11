<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * The stopovers column is no longer required as the data is now stored in the stopovers table.
 * The column is not dropped as it is still required for the migration which should be programmed soon.
 * But "stopovers" collides with the relation, that's why we need to rename the column.
 */
return new class extends Migration
{
    public function up(): void {
        Schema::table('hafas_trips', function(Blueprint $table) {
            // Renaming is not possible as out tests use SQLite which doesn't support this.. So.. recreate...
            // $table->renameColumn('stopovers', 'stopovers_legacy');

            $table->json('stopovers_legacy')
                  ->comment('Unused! Needs to be migrated to table train_stopovers and deleted afterwards.');
        });

        DB::table('hafas_trips')->update(['stopovers_legacy' => DB::raw('stopovers')]);

        Schema::table('hafas_trips', function(Blueprint $table) {
            $table->dropColumn('stopovers');
        });
    }

    public function down(): void {
        Schema::table('hafas_trips', function(Blueprint $table) {
            $table->json('stopovers')->after('destination');
        });

        DB::table('hafas_trips')->update(['stopovers' => DB::raw('stopovers_legacy')]);

        Schema::table('hafas_trips', function(Blueprint $table) {
            $table->dropColumn('stopovers_legacy');
        });
    }
};
