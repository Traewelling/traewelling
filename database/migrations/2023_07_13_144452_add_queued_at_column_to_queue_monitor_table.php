<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddQueuedAtColumnToQueueMonitorTable extends Migration
{
    public function up(): void {
        Schema::table(config('queue-monitor.table'), static function(Blueprint $table) {
            $table->dateTime('queued_at')->nullable()->after('status');
        });
    }

    public function down(): void {
        Schema::table(config('queue-monitor.table'), static function(Blueprint $table) {
            $table->dropColumn('queued_at');
        });
    }
}
