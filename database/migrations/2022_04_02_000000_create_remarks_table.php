<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up(): void {
        Schema::create('remarks', static function(Blueprint $table) {
            $table->id();
            $table->text('text')->nullable();
            $table->string('type')->nullable();
            $table->string('code')->nullable();
            $table->text('summary')->nullable();
            $table->timestamps();

            //TODO: Create unique keys in future migrations.
            // First we have to analyze what the data structure of remarks is.
        });
    }

    public function down(): void {
        Schema::dropIfExists('remarks');
    }
};
