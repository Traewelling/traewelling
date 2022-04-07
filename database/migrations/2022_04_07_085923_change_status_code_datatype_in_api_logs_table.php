<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public static function up(): void {
        Schema::table('api_logs', static function(Blueprint $table) {
            $table->unsignedSmallInteger('status_code')->nullable()->change();
        });
    }

    public function down(): void {
        Schema::table('api_logs', static function(Blueprint $table) {
            $table->unsignedTinyInteger('status_code')->nullable();
        });
    }
};
