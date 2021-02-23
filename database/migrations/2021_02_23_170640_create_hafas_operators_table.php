<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateHafasOperatorsTable extends Migration
{
    public function up(): void {
        Schema::create('hafas_operators', function(Blueprint $table) {
            $table->id();
            $table->string('hafas_id')->unique();
            $table->string('name');
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('hafas_operators');
    }
}
