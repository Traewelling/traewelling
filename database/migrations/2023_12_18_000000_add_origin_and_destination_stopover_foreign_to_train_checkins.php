<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Currently we have origin, destination, departure and arrival in the train_checkins table.
 * This is not well-designed, because we have to store the same information multiple times.
 * This migration adds two new columns for a foreign key to the stopovers table, so we can remove the duplicate
 * information in the future.
 *
 * This is intentionally kept so small to make migration easier.
 * The double columns should be gradually changed in the code.
 */
return new class extends Migration
{
    public function up(): void {
        Schema::table('train_checkins', static function(Blueprint $table) {
            $table->unsignedBigInteger('origin_stopover_id')->nullable()->after('origin');
            $table->unsignedBigInteger('destination_stopover_id')->nullable()->after('destination');

            $table->foreign('origin_stopover_id')->references('id')->on('train_stopovers');
            $table->foreign('destination_stopover_id')->references('id')->on('train_stopovers');
        });
    }

    public function down(): void {
        Schema::table('train_checkins', static function(Blueprint $table) {
            $table->dropForeign(['origin_stopover_id']);
            $table->dropForeign(['destination_stopover_id']);

            $table->dropColumn('origin_stopover_id');
            $table->dropColumn('destination_stopover_id');
        });
    }
};
