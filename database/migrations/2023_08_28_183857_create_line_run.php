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
        Schema::create('line_runs', function (Blueprint $table) {
            $table->id();
            $table->string('hash');
            $table->unsignedBigInteger('line_segment_id');
            $table->timestamps();

            $table->foreign('line_segment_id')
                ->references('id')
                ->on('line_segment_between');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('line_runs');
    }
};
