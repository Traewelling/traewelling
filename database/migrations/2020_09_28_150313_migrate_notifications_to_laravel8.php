<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class MigrateNotificationsToLaravel8 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        DB::table('notifications')
          ->where('notifiable_type', 'App\User')
          ->update([
                       'notifiable_type' => 'App\Models\User'
                   ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        //cannot undo
    }
}
