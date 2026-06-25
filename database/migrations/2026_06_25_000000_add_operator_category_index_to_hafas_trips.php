<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::table('hafas_trips', static function (Blueprint $table) {
            $table->index(['operator_id', 'category'], 'hafas_trips_operator_category_idx');
        });
    }

    public function down(): void
    {
        Schema::table('hafas_trips', static function (Blueprint $table) {
            $table->dropIndex('hafas_trips_operator_category_idx');
        });
    }
};
