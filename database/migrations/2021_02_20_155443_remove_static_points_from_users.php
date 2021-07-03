<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RemoveStaticPointsFromUsers extends Migration
{

    public function up(): void {
        Schema::table('users', function(Blueprint $table) {
            $table->dropColumn('points');
        });
    }

    public function down(): void {
        Schema::table('users', function(Blueprint $table) {
            $table->integer('points')
                  ->unsigned()
                  ->default(0)
                  ->after('train_duration');
        });
    }
}
