<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStatusesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::create('statuses', function(Blueprint $table) {
            $table->id();
            $table->text('body')->nullable();
            $table->integer('user_id')->unsigned();
            $table->boolean('business')->nullable();
            $table->string('type')->default('hafas');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::dropIfExists('statuses');
    }
}
