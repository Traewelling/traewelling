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
        Schema::table('operator_identifiers', function (Blueprint $table) {
            $table->char('operator_uuid', 36)->nullable()->after('operator_id');
        });

        DB::statement('
            UPDATE operator_identifiers
            SET operator_uuid = (SELECT id FROM operators WHERE legacy_id = operator_identifiers.operator_id)
            WHERE operator_id IS NOT NULL
        ');

        Schema::table('operator_identifiers', function (Blueprint $table) {
            $table->dropForeign(['operator_id']);
            $table->dropColumn('operator_id');
            $table->renameColumn('operator_uuid', 'operator_id');
        });

        Schema::table('operator_identifiers', function (Blueprint $table) {
            $table->foreign('operator_id')
                ->references('id')
                ->on('operators')
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('operator_identifiers', function (Blueprint $table) {
            $table->dropForeign(['operator_id']);
            $table->unsignedBigInteger('operator_legacy_id')->nullable()->after('operator_id');
        });

        DB::statement('
            UPDATE operator_identifiers
            SET operator_legacy_id = (SELECT legacy_id FROM operators WHERE id = operator_identifiers.operator_id)
            WHERE operator_id IS NOT NULL
        ');

        Schema::table('operator_identifiers', function (Blueprint $table) {
            $table->dropColumn('operator_id');
            $table->renameColumn('operator_legacy_id', 'operator_id');
        });

        Schema::table('operator_identifiers', function (Blueprint $table) {
            $table->foreign('operator_id')
                ->references('id')
                ->on('hafas_operators')
                ->cascadeOnDelete();
        });
    }
};
