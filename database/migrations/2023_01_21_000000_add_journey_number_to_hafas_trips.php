<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up(): void {
        Schema::table('hafas_trips', static function(Blueprint $table) {
            $table->unsignedInteger('journey_number')->nullable()->after('linename');
        });
    }

    public function down(): void {
        Schema::table('hafas_trips', static function(Blueprint $table) {
            $table->dropColumn('journey_number');
        });
    }
};
