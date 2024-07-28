<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void {
        DB::table('users')
          ->where('name', null)
          ->update(['name' => DB::raw('username')]);

        Schema::table('users', function(Blueprint $table) {
            $table->string('name', 50)->nullable(false)->change();
        });
    }

    public function down(): void {
        Schema::table('users', function(Blueprint $table) {
            $table->string('name')->nullable()->change();
        });
    }
};
