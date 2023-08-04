<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::disableForeignKeyConstraints();
        Schema::table('hafas_trips', function (Blueprint $table) {
            $table->dropColumn('stopovers');
        });
        Schema::enableForeignKeyConstraints();
    }

    public function down(): void
    {
        Schema::table('hafas_trips', function (Blueprint $table) {
            $table->json('stopovers')->nullable();
        });
    }
};
