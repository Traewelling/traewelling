<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void {
        Schema::table('train_checkins', static function(Blueprint $table) {
            $table->dateTime('departure')->nullable()->default(null)->change();
            $table->dateTime('arrival')->nullable()->default(null)->change();
        });
    }

    public function down(): void {

        Schema::table('train_checkins', static function(Blueprint $table) {
            $table->dateTime('departure')->change();
            $table->dateTime('arrival')->change();
        });
    }
};
