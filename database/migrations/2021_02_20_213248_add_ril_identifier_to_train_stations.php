<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddRilIdentifierToTrainStations extends Migration
{

    public function up(): void {
        Schema::table('train_stations', function(Blueprint $table) {
            $table->string('rilIdentifier')
                  ->nullable()
                  ->default(null)
                  ->after('ibnr');
        });
    }

    public function down(): void {
        Schema::table('train_stations', function(Blueprint $table) {
            $table->dropColumn('rilIdentifier');
        });
    }
}
