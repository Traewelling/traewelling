<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void {
        Schema::table('train_checkins', static function(Blueprint $table) {
            $table->renameColumn('real_departure', 'manual_departure');
        });
        Schema::table('train_checkins', static function(Blueprint $table) {
            $table->renameColumn('real_arrival', 'manual_arrival');
        });
    }

    public function down(): void {
        Schema::table('train_checkins', static function(Blueprint $table) {
            $table->renameColumn('manual_arrival', 'real_arrival');
        });
        Schema::table('train_checkins', static function(Blueprint $table) {
            $table->renameColumn('manual_departure', 'real_departure');
        });
    }
};
