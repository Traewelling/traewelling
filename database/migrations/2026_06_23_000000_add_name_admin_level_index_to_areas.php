<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::table('areas', function (Blueprint $table) {
            // Fixes: SELECT * FROM areas WHERE name = ? AND adminLevel = ?
            // Previously: full scan of ~45k rows, 143k calls/day, 3400s wasted.
            $table->index(['name', 'adminLevel'], 'areas_name_admin_level_index');
        });
    }

    public function down(): void
    {
        Schema::table('areas', function (Blueprint $table) {
            $table->dropIndex('areas_name_admin_level_index');
        });
    }
};
