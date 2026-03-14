<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::table('train_checkins', static function (Blueprint $table) {
            $table->index(['user_id', 'departure']);
        });
    }

    public function down(): void
    {
        Schema::table('train_checkins', static function (Blueprint $table) {
            $table->dropIndex(['user_id', 'departure']);
        });
    }
};
