<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up(): void {
        Schema::create('station_names', static function(Blueprint $table) {
            $table->char('id', 36)->primary();
            $table->unsignedBigInteger('station_id');
            $table->string('language');
            $table->string('name');
            $table->timestamps();

            $table->foreign('station_id')->references('id')->on('train_stations')->onDelete('cascade');

            $table->unique(['station_id', 'language']);
        });
    }

    public function down(): void {
        Schema::dropIfExists('station_names');
    }
};
