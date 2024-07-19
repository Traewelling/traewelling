<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void {
        Schema::table('users', function(Blueprint $table) {
            $table->boolean('points_enabled')->default(true)->after('likes_enabled');
        });
    }

    public function down(): void {
        Schema::table('users', function(Blueprint $table) {
            $table->dropColumn('points_enabled');
        });
    }
};
