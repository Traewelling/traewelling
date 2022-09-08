<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up(): void {
        Schema::table('statuses', static function(Blueprint $table) {
            $table->dropColumn('type');
        });
        Schema::table('statuses', static function(Blueprint $table) {
            $table->enum('type', ['hafas', 'location'])->default('hafas')->after('visibility');
        });
    }

    public function down(): void {
        Schema::table('statuses', static function(Blueprint $table) {
            $table->dropColumn('type');
        });
        Schema::table('statuses', static function(Blueprint $table) {
            $table->string('type')->default('hafas')->after('visibility');
        });
    }
};
