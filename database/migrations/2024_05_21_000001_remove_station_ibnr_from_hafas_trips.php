<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void {
        Schema::table('hafas_trips', static function(Blueprint $table) {
            $table->dropForeign(['origin']);
            $table->dropForeign(['destination']);

            $table->dropColumn('origin');
            $table->dropColumn('destination');
        });
    }

    public function down(): void {
        Schema::table('hafas_trips', static function(Blueprint $table) {
            $table->unsignedBigInteger('origin')->nullable()->after('origin_id');
            $table->unsignedBigInteger('destination')->nullable()->after('destination_id');

            $table->foreign('origin')->references('ibnr')->on('train_stations');
            $table->foreign('destination')->references('ibnr')->on('train_stations');
        });
    }
};
