<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * This index is useful to improve the select query in trwl:refreshTrips command.
 */
class AddIndexToHafasTrips extends Migration
{
    public function up(): void {
        Schema::table('hafas_trips', static function(Blueprint $table) {
            $table->index(['created_at', 'trip_id']);
        });
    }

    public function down(): void {
        Schema::table('hafas_trips', static function(Blueprint $table) {
            $table->dropIndex(['created_at', 'trip_id']);
        });
    }
}
