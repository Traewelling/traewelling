<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void {
        // SQLite cannot drop columns without rebuilding tables; skip there to keep local dev easy.
        if (Schema::connection(null)->getConnection()->getDriverName() === 'sqlite') {
            return;
        }

        Schema::table('statuses', static function(Blueprint $table) {
            $table->dropColumn('tweet_id');
        });
    }
    
    public function down(): void {
        // Only add back when column is missing; safe for sqlite
        Schema::table('statuses', static function(Blueprint $table) {
            if (!Schema::hasColumn('statuses', 'tweet_id')) {
                $table->string('tweet_id')->nullable()->after('event_id');
            }
        });
    }
};
