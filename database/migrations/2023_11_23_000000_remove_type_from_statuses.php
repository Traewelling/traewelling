<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * This migration removes the type column from the statuses table.
 * This attribut was planned to differ between hafas and custom statuses, but was never used.
 * It's now planned to merge the status and train_checkins models into one model and create manual trags directly to
 * the trips (currently "hafas_trips") table.
 */
return new class extends Migration
{
    public function up(): void {
        Schema::dropColumns('statuses', ['type']);
    }

    public function down(): void {
        Schema::table('statuses', static function(Blueprint $table) {
            $table->string('type')->default('hafas')->after('visibility');
        });
    }
};
