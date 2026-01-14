<?php

use App\Models\Area;
use App\Models\Station;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::create('areas_stations_maps', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignIdFor(Station::class)->constrained();
            $table->foreignIdFor(Area::class)->constrained();
            $table->boolean('default')->default(false)->comment('Whether it\'s the default area for the station');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('areas_stations_maps');
    }
};
