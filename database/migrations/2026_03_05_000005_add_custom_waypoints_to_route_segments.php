<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::table('route_segments', function (Blueprint $table) {
            $table->json('custom_waypoints')
                ->nullable()
                ->after('path_type')
                ->comment('Custom waypoints [[lat, lng], ...] set via admin map editor');
        });
    }

    public function down(): void
    {
        Schema::table('route_segments', function (Blueprint $table) {
            $table->dropColumn('custom_waypoints');
        });
    }
};
