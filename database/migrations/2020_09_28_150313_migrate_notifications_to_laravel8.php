<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

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
