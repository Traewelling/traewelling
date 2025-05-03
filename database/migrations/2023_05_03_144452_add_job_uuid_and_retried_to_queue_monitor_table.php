<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddJobUuidAndRetriedToQueueMonitorTable extends Migration
{
    public function up(): void {
        Schema::table(config('queue-monitor.table'), static function(Blueprint $table) {
            $table->char('job_uuid', 36)->nullable()->after('id');
            $table->boolean('retried')->default(false)->after('attempt');
        });
    }

    public function down(): void {
        Schema::table(config('queue-monitor.table'), static function(Blueprint $table) {
            $table->dropColumn('job_uuid');
            $table->dropColumn('retried');
        });
    }
}
