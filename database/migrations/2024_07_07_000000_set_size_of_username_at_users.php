<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void {
        Schema::table('users', function(Blueprint $table) {
            $table->string('username', 25)->change();
        });
    }

    public function down(): void {
        Schema::table('users', function(Blueprint $table) {
            $table->string('username')->change();
        });
    }
};
