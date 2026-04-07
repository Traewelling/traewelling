<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class() extends Migration
{
    public function up(): void
    {
        DB::table('hafas_operators')->orderBy('id')->each(function (object $row): void {
            // Migrate existing identifiers to the new OperatorIdentifier model
            if ($row->hafas_id) {
                DB::table('operator_identifiers')->insert([
                    'id' => Str::uuid()->toString(),
                    'operator_id' => $row->id,
                    'identifier' => $row->hafas_id,
                    'type' => 'hafas',
                    'source' => 'hafas',
                    'name' => $row->name,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            if ($row->motis_id) {
                // Only create motis identifier if it exists
                DB::table('operator_identifiers')->insert([
                    'id' => Str::uuid()->toString(),
                    'operator_id' => $row->id,
                    'identifier' => $row->motis_id,
                    'type' => 'motis',
                    'source' => $row->motis_source ?? 'transitous',
                    'name' => $row->name,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        });

        Schema::table('hafas_operators', function (Blueprint $table) {
            $table->dropUnique(['motis_id']);
            $table->dropColumn(['motis_id']);
            $table->dropUnique(['hafas_id']);
            $table->dropColumn(['hafas_id']);
            $table->dropColumn(['motis_source']);
        });
    }

    public function down(): void
    {
        Schema::table('hafas_operators', function (Blueprint $table) {
            $table->string('motis_source')->nullable()->after('name');
            $table->string('hafas_id')->unique()->nullable()->after('motis_source');
            $table->string('motis_id')->unique()->nullable()->after('hafas_id');
        });

        DB::table('hafas_operators')->orderBy('id')->each(function (object $row): void {
            $hafas = DB::table('operator_identifiers')
                ->where('operator_id', $row->id)
                ->where('type', 'hafas')
                ->first();

            $motis = DB::table('operator_identifiers')
                ->where('operator_id', $row->id)
                ->where('type', 'motis')
                ->first();

            if ($hafas || $motis) {
                DB::table('hafas_operators')->where('id', $row->id)->update(array_filter([
                    'hafas_id' => $hafas?->identifier,
                    'motis_id' => $motis?->identifier,
                    'motis_source' => $motis?->source,
                ]));
            }
        });
    }
};
