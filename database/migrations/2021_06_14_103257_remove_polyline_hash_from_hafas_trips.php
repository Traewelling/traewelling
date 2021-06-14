<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Schema;

class RemovePolylineHashFromHafasTrips extends Migration
{

    public function up(): void {
        Schema::table('hafas_trips', function(Blueprint $table) {
            if (!App::runningUnitTests()) {
                $table->dropForeign(['polyline']);
            }
            $table->dropColumn(['polyline']);
        });
    }

    public function down(): void {
        Schema::table('hafas_trips', function(Blueprint $table) {
            $table->string('polyline')->after('stopovers');
            $table->foreign('polyline')
                  ->references('hash')
                  ->on('poly_lines');
        });
    }
}
