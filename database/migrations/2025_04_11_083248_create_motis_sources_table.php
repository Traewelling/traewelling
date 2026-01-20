<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::create('motis_source_licenses', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('provider')->nullable();
            $table->string('country')->nullable();
            $table->string('name')->nullable();
            $table->string('license')->nullable();
            $table->string('license_url')->nullable();
            $table->string('source_url')->nullable();
            $table->string('spdx')->nullable();
            $table->boolean('active')->default(false);
            $table->timestamps();

            $table->index(['provider', 'country', 'name', 'active'], 'motis_sources_provider_country_name_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('motis_source_licenses');
    }
};
