<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        // SQLite (used in tests) does not support JSON virtual columns — skip silently.
        if (DB::connection()->getDriverName() === 'sqlite') {
            return;
        }

        // Laravel translates ->where('data->status->id', $id) to:
        //   WHERE json_unquote(json_extract(`data`, '$."status"."id"')) = ?
        //
        // The virtual column uses the identical expression so MariaDB's optimizer
        // automatically uses the composite index when that WHERE clause appears.
        //
        // Fixes DeleteStatusNotifications job (type IN [UserJoinedConnection, StatusLiked]),
        // previously a full scan of ~200k rows, 980 calls/day, 1144s wasted.
        DB::statement(
            "ALTER TABLE `notifications`
             ADD COLUMN `data_status_id` BIGINT UNSIGNED GENERATED ALWAYS AS (
                 json_unquote(json_extract(`data`, '$.\"status\".\"id\"'))
             ) VIRTUAL"
        );

        Schema::table('notifications', function (Blueprint $table) {
            $table->index(['type', 'data_status_id'], 'notifications_type_data_status_id_index');
        });
    }

    public function down(): void
    {
        if (DB::connection()->getDriverName() === 'sqlite') {
            return;
        }

        Schema::table('notifications', function (Blueprint $table) {
            $table->dropIndex('notifications_type_data_status_id_index');
            $table->dropColumn('data_status_id');
        });
    }
};
