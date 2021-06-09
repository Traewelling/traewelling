<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIndexToSessionsTable extends Migration
{

    public function up(): void {
        Schema::table('sessions', function(Blueprint $table) {
            $table->dropUnique(['id']);
            $table->primary('id');

            $table->index(['user_id']);
            $table->index(['last_activity']);
        });
    }

    public function down(): void {
        Schema::table('sessions', function(Blueprint $table) {
            $table->dropPrimary(['id']);
            $table->unique('id');

            $table->dropIndex(['user_id']);
            $table->dropIndex(['last_activity']);
        });
    }
}
