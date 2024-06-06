<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void {
        Schema::table('train_checkins', static function(Blueprint $table) {
            $table->dropForeign(['origin']);
            $table->dropForeign(['destination']);
            $table->dropIndex('user_trip_origin_departure');

            $table->dropColumn('origin');
            $table->dropColumn('destination');
        });
    }
};
