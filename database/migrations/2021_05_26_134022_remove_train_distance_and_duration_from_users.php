<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RemoveTrainDistanceAndDurationFromUsers extends Migration
{

    public function up(): void {
        Schema::table('users', function(Blueprint $table) {
            $table->dropColumn(['train_distance', 'train_duration']);
        });
    }

    public function down(): void {
        Schema::table('users', function(Blueprint $table) {
            $table->float('train_distance')->default(0.00);
            $table->unsignedInteger('train_duration')->default(0);
        });
    }
}
