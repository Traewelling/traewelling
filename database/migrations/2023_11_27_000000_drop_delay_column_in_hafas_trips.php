<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void {
        if (Schema::hasColumn('hafas_trips', 'delay')) {
            Schema::table('hafas_trips', static function(Blueprint $table) {
                $table->dropColumn('delay');
            });
        }
    }

    public function down(): void {
        Schema::table('hafas_trips', static function(Blueprint $table) {
            $table->integer('delay')->nullable()->after('arrival');
        });
    }
};
