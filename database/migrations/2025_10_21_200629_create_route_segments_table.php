<?php

use App\Models\Station;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('route_segments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignIdFor(Station::class, 'from_station_id')->constrained()->onDelete('cascade');
            $table->foreignIdFor(Station::class, 'to_station_id')->constrained()->onDelete('cascade');
            $table->unsignedInteger('distance')->comment('Distance in meters');
            $table->unsignedInteger('duration')->comment('Duration in seconds');
            $table->string('path_type')->comment('Type of routing path, e.g., rail, road, trail')->nullable();
            $table->string('polyline', 10000);
            $table->integer('polyline_precision')->default(5);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('route_segments');
    }
};
