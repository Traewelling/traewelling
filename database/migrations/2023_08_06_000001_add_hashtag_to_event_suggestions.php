<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void {
        Schema::table('event_suggestions', static function(Blueprint $table) {
            $table->string('hashtag')->nullable()->after('end');
        });
    }

    public function down(): void {
        Schema::table('event_suggestions', static function(Blueprint $table) {
            $table->dropColumn('hashtag');
        });
    }
};
