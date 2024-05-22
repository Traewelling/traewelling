<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up(): void {
        Schema::table('queue_monitor', static function(Blueprint $table) {
            $table->index(['name', 'status', 'queue']);
        });
    }

    public function down(): void {
        Schema::table('queue_monitor', static function(Blueprint $table) {
            $table->dropIndex(['name', 'status', 'queue']);
        });
    }
};
