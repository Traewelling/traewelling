<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AddUserIdToTrainCheckins extends Migration
{

    public function up(): void {
        Schema::table('train_checkins', function(Blueprint $table) {
            $table->unsignedBigInteger('user_id')
                  ->nullable()
                  ->comment('workaround for unique key')
                  ->after('status_id');

            $table->foreign('user_id')
                  ->references('id')
                  ->on('users')
                  ->cascadeOnDelete();
        });

        DB::table('train_checkins')
          ->update([
                       'user_id' => DB::raw('(SELECT user_id FROM statuses WHERE statuses.id = train_checkins.status_id)'),
                   ]);
    }

    public function down(): void {
        Schema::table('train_checkins', function(Blueprint $table) {
            $table->dropColumn(['user_id']);
        });
    }
}
