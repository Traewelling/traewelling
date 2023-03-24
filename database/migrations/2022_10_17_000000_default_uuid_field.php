<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void {
        Schema::table('failed_jobs', static function(Blueprint $table) {
            $table->string('uuid')->default('UUID()')->change();
        });
    }

    public function down(): void {
        Schema::table('failed_jobs', static function(Blueprint $table) {
            $table->string('uuid')->default(null)->change();
        });
    }
};
