<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up(): void {
        Schema::create('trip_remarks', static function(Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('trip_id');
            $table->unsignedBigInteger('remark_id');
            $table->timestamps();

            $table->unique(['trip_id', 'remark_id']);

            $table->foreign('trip_id')->references('id')->on('hafas_trips');
            $table->foreign('remark_id')->references('id')->on('remarks');
        });
    }

    public function down(): void {
        Schema::dropIfExists('trip_remarks');
    }
};
