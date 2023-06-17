<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up(): void {
        Schema::table('train_checkins', function(Blueprint $table) {
            $table->timestamp('real_departure')
                  ->comment('User-defined override of the departure')
                  ->default(null)
                  ->nullable()
                  ->after('departure');
            $table->timestamp('real_arrival')
                  ->comment('User-defined override of the arrival')
                  ->default(null)
                  ->nullable()
                  ->after('arrival');
        });
    }

    public function down(): void {
        Schema::table('train_checkins', function(Blueprint $table) {
            $table->dropColumn(['real_departure', 'real_arrival']);
        });
    }
};
