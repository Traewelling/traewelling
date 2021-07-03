<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

class DropPointsCalculationTable extends Migration
{

    public function up(): void {
        Schema::dropIfExists('pointscalculation');
    }

    public function down(): void {
        Schema::create('pointscalculation', function(Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('type');
            $table->string('transport_type')->nullable();
            $table->integer('value');
            $table->timestamps();
        });
    }
}
