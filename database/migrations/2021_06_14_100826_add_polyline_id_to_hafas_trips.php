<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AddPolylineIdToHafasTrips extends Migration
{

    //TODO: Delete old column 'polyline' if migration succeeded
    public function up(): void {
        Schema::table('hafas_trips', function(Blueprint $table) {
            $table->unsignedBigInteger('polyline_id')
                  ->nullable()
                  ->default(null)
                  ->after('polyline');

            $table->foreign('polyline_id')
                  ->references('id')
                  ->on('poly_lines');
        });

        $fetchQuery = DB::raw('(SELECT id FROM poly_lines WHERE poly_lines.hash = hafas_trips.polyline)');
        DB::table('hafas_trips')->update(['polyline_id' => $fetchQuery]);
    }

    public function down(): void {
        Schema::table('hafas_trips', function(Blueprint $table) {
            $table->dropForeign(['polyline_id']);
            $table->dropColumn('polyline_id');
        });
    }
}
