<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RemoveDelayFromTrainCheckins extends Migration
{

    public function up(): void {
        Schema::table('train_checkins', function(Blueprint $table) {
            $table->dropColumn(['delay']);
        });
    }

    public function down(): void {
        Schema::table('train_checkins', function(Blueprint $table) {
            $table->integer('delay')->nullable()->after('points');
        });
    }
}
