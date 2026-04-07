<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('hafas_operators');
    }

    public function down(): void
    {
        Schema::create('hafas_operators', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name');
            $table->string('wikidata_id')->nullable();
            $table->timestamps();
        });

        // see 2026_04_07_000001 rollback.
    }
};
