<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Some legacy functions store large session payloads,
 * so this is a migration to change the payload column to longText to prevent 500 errors,
 * when we flash large data into the session.
 */
return new class extends Migration
{
    public function up(): void {
        Schema::table('sessions', function(Blueprint $table) {
            $table->longText('payload')->change();
        });
    }

    public function down(): void {
        Schema::table('sessions', function(Blueprint $table) {
            $table->text('payload')->change();
        });
    }
};
