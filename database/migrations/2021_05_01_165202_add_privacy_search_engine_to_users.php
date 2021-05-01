<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPrivacySearchEngineToUsers extends Migration
{
    public function up(): void {
        Schema::table('users', function(Blueprint $table) {
            $table->boolean('prevent_index')
                  ->comment('prevent search engines from indexing this profile')
                  ->default(0)
                  ->after('private_profile');
        });
    }

    public function down(): void {
        Schema::table('users', function(Blueprint $table) {
            $table->dropColumn('prevent_index');
        });
    }
}
