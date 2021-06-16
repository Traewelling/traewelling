<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePolyLinesTable extends Migration
{

    public function up(): void {
        Schema::create('poly_lines', function(Blueprint $table) {
            $table->id();
            $table->string('hash')->unique();
            $table->json('polyline');
            $table->timestamps();
        });

        //TODO: Move to hafas_trips table creation after resorting
        Schema::table('hafas_trips', function(Blueprint $table) {
            $table->foreign('polyline_id')
                  ->references('id')
                  ->on('poly_lines');
        });
    }

    public function down(): void {
        Schema::dropIfExists('poly_lines');
    }
}
