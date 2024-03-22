<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void {
        Schema::table('hafas_trips', static function(Blueprint $table) {
            //Columns need to be nullable for the migration
            $table->unsignedBigInteger('origin_id')->nullable()->after('origin');
            $table->unsignedBigInteger('destination_id')->nullable()->after('destination');

            $table->foreign('origin_id')->references('id')->on('train_stations');
            $table->foreign('destination_id')->references('id')->on('train_stations');
        });

    }

    public function down(): void {
        Schema::table('hafas_trips', static function(Blueprint $table) {
            $table->dropForeign(['origin_id']);
            $table->dropForeign(['destination_id']);
            $table->dropColumn('origin_id');
            $table->dropColumn('destination_id');
        });
    }
};
