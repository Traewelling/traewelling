<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddForeignKeysToEvents extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('events', function (Blueprint $table) {
            $table->bigInteger('trainstation')->unsigned()->change();

            $table->foreign('trainstation')
                ->references('id')
                ->on('train_stations')
                ->onDelete('restrict')
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
        Schema::table('events', function (Blueprint $table) {
            $table->dropForeign("events_trainstation_foreign");
        });
        Schema::table('events', function (Blueprint $table) {
            $table->integer('trainstation')->change();
        });
    }
}
