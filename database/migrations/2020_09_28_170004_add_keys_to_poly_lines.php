<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddKeysToPolyLines extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::table('poly_lines', function (Blueprint $table) {
            $table->index('hash'); //Can be maybe unique, but not required here... So it can be better because of MD5 Collisions
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::table('poly_lines', function (Blueprint $table) {
            $table->dropIndex("poly_lines_hash_index");
        });
    }
}
