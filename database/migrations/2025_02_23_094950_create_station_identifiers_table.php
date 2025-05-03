<?php

use App\Models\Station;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void {
        Schema::create('station_identifiers', function(Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignIdFor(Station::class)->constrained();
            $table->string('type');
            $table->string('origin')->nullable();
            $table->string('identifier');
            $table->string('name')->nullable()->comment('Name of the station provided by the data source');
            $table->timestamps();

            $table->index(['type', 'origin', 'identifier']);
        });
    }

    public function down(): void {
        Schema::dropIfExists('station_identifiers');
    }
};
