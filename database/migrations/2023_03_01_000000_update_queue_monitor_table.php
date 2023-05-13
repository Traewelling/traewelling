<?php

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use romanzipp\QueueMonitor\Enums\MonitorStatus;
use romanzipp\QueueMonitor\Models\Monitor;

class UpdateQueueMonitorTable extends Migration
{
    public function up(): void {
        Schema::table(config('queue-monitor.table'), static function(Blueprint $table) {
            $table->unsignedInteger('status')->default(MonitorStatus::RUNNING)->after('queue');
        });

        $this->upgradeColumns();

        Schema::table(config('queue-monitor.table'), static function(Blueprint $table) {
            $table->dropColumn(['failed', 'time_elapsed']);
        });
    }

    public function upgradeColumns(): void {
        Monitor::query()->chunk(500, function(Collection $monitors) {
            /** @var array<int, array<Monitor>> $matrix */
            $matrix = [
                MonitorStatus::RUNNING   => [],
                MonitorStatus::FAILED    => [],
                MonitorStatus::SUCCEEDED => [],
            ];

            foreach ($monitors as $monitor) {
                /** @phpstan-ignore-next-line */
                if ($monitor->failed) {
                    $matrix[MonitorStatus::FAILED][] = $monitor;
                } elseif (null !== $monitor->finished_at) {
                    $matrix[MonitorStatus::SUCCEEDED][] = $monitor;
                } else {
                    $matrix[MonitorStatus::RUNNING][] = $monitor;
                }
            }

            foreach ($matrix as $status => $monitors) {
                DB::table(config('queue-monitor.table'))
                  ->whereIn('id', array_map(static fn(Monitor $monitor) => $monitor->id, $monitors))
                  ->update(['status' => $status]);
            }
        });
    }

    public function down(): void {
        Schema::table(config('queue-monitor.table'), static function(Blueprint $table) {
            $table->dropColumn('status');

            $table->float('time_elapsed', 12, 6)->nullable()->index();
            $table->boolean('failed')->default(false)->index();
        });
    }
}
