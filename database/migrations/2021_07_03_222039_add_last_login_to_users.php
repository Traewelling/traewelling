<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AddLastLoginToUsers extends Migration
{

    public function up(): void {
        Schema::table('users', function(Blueprint $table) {
            $table->timestamp('last_login')
                  ->nullable()
                  ->default(null)
                  ->after('remember_token');
        });

        DB::table('users')->update([
                                       'last_login' => DB::raw('updated_at')
                                   ]);
    }

    public function down(): void {
        Schema::table('users', function(Blueprint $table) {
            $table->dropColumn('last_login');
        });
    }
}
