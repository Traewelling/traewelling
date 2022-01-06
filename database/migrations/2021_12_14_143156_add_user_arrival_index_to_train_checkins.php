<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * This index is useful for the performance of "getLatestArrivals".
 */
class AddUserArrivalIndexToTrainCheckins extends Migration
{

    public function up(): void {
        Schema::table('train_checkins', function(Blueprint $table) {
            $table->index(['user_id', 'arrival']);
        });
    }

    public function down(): void {
        Schema::table('train_checkins', function(Blueprint $table) {
            $table->dropIndex(['user_id', 'arrival']);
        });
    }
}
