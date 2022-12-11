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
            $table->renameColumn('stopovers', 'stopovers_legacy');
        });
    }

    public function down(): void {
        Schema::table('hafas_trips', function(Blueprint $table) {
            $table->renameColumn('stopovers_legacy', 'stopovers');
        });
    }
};
