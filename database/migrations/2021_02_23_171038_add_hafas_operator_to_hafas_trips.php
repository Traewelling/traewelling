<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddHafasOperatorToHafasTrips extends Migration
{
    public function up(): void {
        Schema::table('hafas_trips', function(Blueprint $table) {
            $table->unsignedBigInteger('operator_id')
                  ->nullable()
                  ->default(null)
                  ->after('linename');

            $table->foreign('operator_id')
                  ->references('id')
                  ->on('hafas_operators');
        });
    }

    public function down(): void {
        Schema::table('hafas_trips', function(Blueprint $table) {
            $table->dropColumn('operator_id');
        });
    }
}
