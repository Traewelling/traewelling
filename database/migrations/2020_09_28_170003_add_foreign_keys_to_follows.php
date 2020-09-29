<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddForeignKeysToFollows extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::table('follows', function (Blueprint $table) {
            $table->bigInteger('user_id')->unsigned()->change();
            $table->bigInteger('follow_id')->unsigned()->change();

            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->onDelete('cascade')
                ->onUpdate('cascade');
            $table->foreign('follow_id')
                ->references('id')
                ->on('users')
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
        Schema::table('follows', function (Blueprint $table) {
            $table->dropForeign("follows_user_id_foreign");
            $table->dropForeign("follows_follow_id_foreign");
        });
        Schema::table('follows', function (Blueprint $table) {
            $table->integer('user_id')->unsigned()->change();
            $table->integer('follow_id')->change();
        });
    }
}
