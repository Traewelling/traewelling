<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePolyLinesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::create('poly_lines', function(Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('hash');
            $table->json('polyline');
            $table->timestamps();
        });

        Schema::table('hafas_trips', function(Blueprint $table) {
            $table->foreign('polyline_id')
                  ->references('id')
                  ->on('poly_lines');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::dropIfExists('poly_lines');
    }
}
