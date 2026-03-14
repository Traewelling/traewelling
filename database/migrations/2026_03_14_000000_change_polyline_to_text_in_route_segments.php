<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::table('route_segments', function (Blueprint $table): void {
            $table->text('polyline')->change();
        });
    }

    public function down(): void
    {
        Schema::table('route_segments', function (Blueprint $table): void {
            $table->string('polyline', 10000)->change();
        });
    }
};
