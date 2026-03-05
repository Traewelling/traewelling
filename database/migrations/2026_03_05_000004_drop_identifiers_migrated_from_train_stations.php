<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::table('train_stations', function (Blueprint $table): void {
            $table->dropColumn('identifiers_migrated');
        });
    }

    public function down(): void
    {
        Schema::table('train_stations', function (Blueprint $table): void {
            $table->boolean('identifiers_migrated')->default(false);
        });
    }
};
