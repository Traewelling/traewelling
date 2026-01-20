<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::table('hafas_operators', function (Blueprint $table) {
            $table->string('hafas_id')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('hafas_operators', function (Blueprint $table) {
            $table->string('hafas_id')->nullable(false)->change();
        });
    }
};
