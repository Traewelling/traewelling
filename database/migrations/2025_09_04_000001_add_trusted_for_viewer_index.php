<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// TODO: check if this is REALLY required on prod before merge
return new class extends Migration {
    public function up(): void {
        Schema::table('trusted_users', function(Blueprint $table) {
            $table->index(['trusted_id', 'user_id', 'expires_at']);
        });
    }

    public function down(): void {
        Schema::table('trusted_users', function(Blueprint $table) {
            $table->dropIndex(['trusted_id', 'user_id', 'expires_at']);
        });
    }
};
