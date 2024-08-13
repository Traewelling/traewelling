<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void {
        Schema::table('statuses', static function(Blueprint $table) {
            $table->dropColumn('tweet_id');
        });
    }
    
    public function down(): void {
        Schema::table('statuses', static function(Blueprint $table) {
            $table->string('tweet_id')->nullable()->after('event_id');
        });
    }
};
