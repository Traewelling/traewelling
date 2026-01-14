<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::create('operator_identifiers', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignId('operator_id')
                ->constrained('hafas_operators')
                ->cascadeOnDelete();
            $table->string('type')->comment('e.g. hafas, motis');
            $table->string('identifier');
            $table->string('source')->nullable()->comment('Source of the identifier, e.g. motis_source');
            $table->string('name')->nullable();
            $table->timestamps();

            $table->unique(['type', 'identifier', 'source', 'name'], 'unique_operator_identifier');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('operator_identifiers');
    }
};
