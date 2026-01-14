<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::create('licenses', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name')->nullable();
            $table->string('human_name')->nullable();
            $table->string('attribution')->nullable();
            $table->string('license_url')->nullable();
            $table->string('source_url')->nullable();
            $table->string('spdx')->nullable();
            $table->boolean('automatically_activate_source')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('licenses');
    }
};
