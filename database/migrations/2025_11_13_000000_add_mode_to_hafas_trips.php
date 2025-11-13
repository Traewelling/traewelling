<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('hafas_trips', function (Blueprint $table) {
            $table->string('mode')->nullable()->comment('MotisCategory enum value');
        });
    }

    public function down(): void
    {
        Schema::table('hafas_trips', function (Blueprint $table) {
            $table->dropColumn('mode');
        });
    }
};
