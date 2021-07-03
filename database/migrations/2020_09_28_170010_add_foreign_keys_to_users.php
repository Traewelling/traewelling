<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddForeignKeysToUsers extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::table('users', function(Blueprint $table) {
            $table->bigInteger('home_id')->unsigned()->nullable()->default(null)->change();
        });
        DB::table('users')->where('home_id', '0')->update(['home_id' => null]);
        Schema::table('users', function(Blueprint $table) {
            $table->foreign('home_id')
                  ->references('id')
                  ->on('train_stations')
                  ->onDelete('set null')
                  ->onUpdate('restrict');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::table('users', function(Blueprint $table) {
            $table->dropForeign("users_home_id_foreign");
        });
        Schema::table('users', function(Blueprint $table) {
            $table->integer('home_id')->unsigned()->default(0)->change();
        });
    }
}
