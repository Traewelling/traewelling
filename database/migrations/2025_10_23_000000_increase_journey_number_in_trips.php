<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void {
        Schema::table('hafas_trips', function(Blueprint $table) {
            $table->unsignedBigInteger('journey_number')->nullable()->change();
        });
    }

    public function down(): void {
        Schema::table('trips', function(Blueprint $table) {
            $table->unsignedInteger('journey_number')->nullable()->change();
        });
    }
};
