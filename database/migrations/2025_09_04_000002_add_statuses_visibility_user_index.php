<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// TODO: check if this is REALLY required on prod before merge
return new class extends Migration {
    public function up(): void {
        Schema::table('statuses', function(Blueprint $table) {
            $table->index(['visibility', 'user_id']);
        });
    }

    public function down(): void {
        Schema::table('statuses', function(Blueprint $table) {
            $table->dropIndex(['visibility', 'user_id']);
        });
    }
};
