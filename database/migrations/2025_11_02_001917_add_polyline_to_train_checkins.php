<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('train_checkins', function (Blueprint $table) {
            $table->longText('encoded_polyline')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('train_checkins', function (Blueprint $table) {
            $table->dropColumn('encoded_polyline');
        });
    }
};
