<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        // Allow string (UUID) values in subject_id and causer_id
        Schema::table('activity_log', static function (Blueprint $table) {
            $table->string('subject_id')->nullable()->change();
            $table->string('causer_id')->nullable()->change();
        });

        // Remap existing Report activity_log entries from integer ID to UUID.
        // Must run while reports still has both `id` (integer) and `uuid` columns.
        DB::table('reports')->select('id', 'uuid')->get()->each(static function (object $report): void {
            DB::table('activity_log')
                ->where('subject_type', 'App\\Models\\Report')
                ->where('subject_id', (string) $report->id)
                ->update(['subject_id' => $report->uuid]);
        });
    }

    public function down(): void
    {
        // Data update is irreversible — integer subject_ids for Report entries are gone
        Schema::table('activity_log', static function (Blueprint $table) {
            $table->unsignedBigInteger('subject_id')->nullable()->change();
            $table->unsignedBigInteger('causer_id')->nullable()->change();
        });
    }
};
