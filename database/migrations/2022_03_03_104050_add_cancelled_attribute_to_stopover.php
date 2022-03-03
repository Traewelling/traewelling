<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void {
        Schema::table('train_stopovers', static function(Blueprint $table) {
            $table->boolean('cancelled')->default(false)->after('departure_platform_real');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void {
        Schema::table('train_stopovers', static function(Blueprint $table) {
            $table->dropColumn('cancelled');
        });
    }
};
