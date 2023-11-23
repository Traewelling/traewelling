<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void {
        Schema::table('hafas_trips', static function(Blueprint $table) {
            $table->unsignedBigInteger('user_id')
                  ->nullable()
                  ->default(null)
                  ->comment('if not null, this trip belongs to the user (e.g. manually created trips)')
                  ->after('source');

            $table->foreign('user_id')
                  ->references('id')
                  ->on('users')
                  ->onDelete('set null');
            //only set to null, because the trip can also be used by other users.
            //if the user is deleted and no other checkins are using this trip, the trip will be deleted automatically by our nightly cleanup job
        });
    }

    public function down(): void {
        Schema::table('hafas_trips', function(Blueprint $table) {
            $table->dropColumn('user_id');
        });
    }
};
