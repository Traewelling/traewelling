<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddLastAccessedAndNameToIcsTokens extends Migration
{

    public function up(): void {
        Schema::table('ics_tokens', function(Blueprint $table) {
            $table->string('name')
                  ->default(null)
                  ->nullable()
                  ->after('user_id');
            $table->timestamp('last_accessed')
                  ->default(null)
                  ->nullable()
                  ->after('token');
        });
    }


    public function down(): void {
        Schema::table('ics_tokens', function(Blueprint $table) {
            $table->dropColumn(['name', 'last_accessed']);
        });
    }
}
