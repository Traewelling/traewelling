<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void {
        Schema::table('hafas_trips', function(Blueprint $table) {
            $table->string('route_color', 6)->nullable()->after('linename')->comment('Hex color code of the route, without #');
        });
    }

    public function down(): void {
        Schema::table('hafas_trips', function(Blueprint $table) {
            $table->dropColumn('route_color');
        });
    }
};
