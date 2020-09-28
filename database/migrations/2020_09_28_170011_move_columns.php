<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class MoveColumns extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        return;
        DB::statement("ALTER TABLE `follows` CHANGE `created_at` `created_at` TIMESTAMP NULL DEFAULT NULL AFTER `follow_id`;");
        DB::statement("ALTER TABLE `follows` CHANGE `updated_at` `updated_at` TIMESTAMP NULL DEFAULT NULL AFTER `created_at`;");

        DB::statement("ALTER TABLE `likes` CHANGE `created_at` `created_at` TIMESTAMP NULL DEFAULT NULL AFTER `status_id`;");
        DB::statement("ALTER TABLE `likes` CHANGE `updated_at` `updated_at` TIMESTAMP NULL DEFAULT NULL AFTER `created_at`;");

        DB::statement("ALTER TABLE `statuses` CHANGE `created_at` `created_at` TIMESTAMP NULL DEFAULT NULL AFTER `event_id`;");
        DB::statement("ALTER TABLE `statuses` CHANGE `updated_at` `updated_at` TIMESTAMP NULL DEFAULT NULL AFTER `created_at`;");

        DB::statement("ALTER TABLE `users` CHANGE `role` `role` TINYINT NOT NULL DEFAULT '0' AFTER `id`;");

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
