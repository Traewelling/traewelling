<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddForeignKeysToStatuses extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('statuses', function (Blueprint $table) {
            $table->bigInteger('user_id')->unsigned()->change();

            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->onDelete('cascade')
                ->onUpdate('cascade');
            $table->foreign('event_id')
                ->references('id')
                ->on('events')
                ->onDelete('set null')
                ->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('statuses', function (Blueprint $table) {
            $table->dropForeign("statuses_user_id_foreign");
            $table->dropForeign("statuses_event_id_foreign");
        });
        Schema::table('statuses', function (Blueprint $table) {
            $table->integer('user_id')->unsigned()->change();
        });
    }
}
