<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddForeignKeysToLikes extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::table('likes', function (Blueprint $table) {
            $table->bigInteger('user_id')->unsigned()->change();
            $table->bigInteger('status_id')->unsigned()->change();

            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->onDelete('cascade')
                ->onUpdate('cascade');
            $table->foreign('status_id')
                ->references('id')
                ->on('statuses')
                ->onDelete('cascade')
                ->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::table('likes', function (Blueprint $table) {
            $table->dropForeign("likes_user_id_foreign");
            $table->dropForeign("likes_status_id_foreign");
        });
        Schema::table('likes', function (Blueprint $table) {
            $table->integer('user_id')->unsigned()->change();
            $table->integer('status_id')->unsigned()->change();
        });
    }
}
