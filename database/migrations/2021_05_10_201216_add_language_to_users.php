<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddLanguageToUsers extends Migration
{

    public function up(): void {
        Schema::table('users', function(Blueprint $table) {
            $table->string('language', 12)
                  ->default(null)
                  ->nullable()
                  ->after('role');
        });
    }

    public function down(): void {
        Schema::table('users', function(Blueprint $table) {
            $table->dropColumn('language');
        });
    }
}
