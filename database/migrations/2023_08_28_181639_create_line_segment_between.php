<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('line_segment_between', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('origin_id');
            $table->unsignedBigInteger('destination_id');
            $table->unsignedBigInteger('segment_id');
            $table->timestamps();

            $table->foreign('origin_id')
                  ->references('id')
                  ->on('train_stations');
            $table->foreign('destination_id')
                  ->references('id')
                  ->on('train_stations');
            $table->foreign('segment_id')
                  ->references('id')
                  ->on('line_segments');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('line_segment_between');
    }
};
