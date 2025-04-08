<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void {
        Schema::table('users', function(Blueprint $table) {
            $table->timestamp('recent_gdpr_export')->nullable()->after('last_login');
        });
    }

    public function down(): void {
        Schema::table('users', function(Blueprint $table) {
            $table->dropColumn('recent_gdpr_export');
        });
    }
};
