<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class PointsCalculation extends Migration
{

    public function up(): void {
        Schema::create('pointscalculation', function(Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('type');
            $table->string('transport_type')->nullable();
            $table->integer('value');
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('pointscalculation');
    }
}
