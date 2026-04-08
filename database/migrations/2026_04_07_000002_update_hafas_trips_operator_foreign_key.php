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
        Schema::table('hafas_trips', function (Blueprint $table) {
            $table->char('operator_uuid', 36)->nullable()->after('operator_id');
        });

        DB::statement('
            UPDATE hafas_trips
            SET operator_uuid = (SELECT id FROM operators WHERE legacy_id = hafas_trips.operator_id)
            WHERE operator_id IS NOT NULL
        ');

        Schema::table('hafas_trips', function (Blueprint $table) {
            $table->dropForeign(['operator_id']);
            $table->dropIndex('hafas_trips_operator_id_category_index');
            $table->dropColumn('operator_id');
            $table->renameColumn('operator_uuid', 'operator_id');
        });

        Schema::table('hafas_trips', function (Blueprint $table) {
            $table->foreign('operator_id')
                ->references('id')
                ->on('operators')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('hafas_trips', function (Blueprint $table) {
            $table->dropForeign(['operator_id']);
            $table->unsignedBigInteger('operator_legacy_id')->nullable()->after('operator_id');
        });

        DB::statement('
            UPDATE hafas_trips
            SET operator_legacy_id = (SELECT legacy_id FROM operators WHERE id = hafas_trips.operator_id)
            WHERE operator_id IS NOT NULL
        ');

        Schema::table('hafas_trips', function (Blueprint $table) {
            $table->dropColumn('operator_id');
            $table->renameColumn('operator_legacy_id', 'operator_id');
        });

        Schema::table('hafas_trips', function (Blueprint $table) {
            $table->foreign('operator_id')
                ->references('id')
                ->on('hafas_operators')
                ->nullOnDelete();
        });
    }
};
