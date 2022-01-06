<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * This index is useful to improve the select query in trwl:refreshTrips command.
 */
class AddIndexToTrainStopovers extends Migration
{
    public function up(): void {
        Schema::table('train_stopovers', static function(Blueprint $table) {
            $table->index(['arrival_planned', 'arrival_real']);
        });
    }

    public function down(): void {
        Schema::table('train_stopovers', static function(Blueprint $table) {
            $table->dropIndex(['arrival_planned', 'arrival_real']);
        });
    }
}
