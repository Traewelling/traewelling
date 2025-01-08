<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up(): void {
        Schema::table('train_stations', function(Blueprint $table) {
            $table->string('source')->nullable()->after('longitude');
        });
    }

    public function down(): void {
        Schema::table('train_stations', function(Blueprint $table) {
            $table->dropColumn('source');
        });
    }
};
