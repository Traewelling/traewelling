<?php

use App\Enum\DataProvider;
use App\Models\Operator;
use App\Models\OperatorIdentifier;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        foreach (Operator::all() as $operator) {
            // Migrate existing identifiers to the new OperatorIdentifier model
            if ($operator->hafas_id) {
                OperatorIdentifier::create([
                    'operator_id' => $operator->id,
                    'identifier' => $operator->hafas_id,
                    'type' => 'hafas',
                    'source' => 'hafas',
                    'name' => $operator->name,
                ]);
            }

            if ($operator->motis_id) {
                // Only create motis identifier if it exists
                OperatorIdentifier::create([
                    'operator_id' => $operator->id,
                    'identifier' => $operator->motis_id,
                    'type' => 'motis',
                    'source' => $operator->motis_source ?? DataProvider::TRANSITOUS,
                    'name' => $operator->name,
                ]);
            }
        }

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

        foreach (Operator::all() as $operator) {
            try {
                $identifier = OperatorIdentifier::where('operator_id', $operator->id)
                    ->where('type', 'hafas')
                    ->first();
                if ($identifier) {
                    $operator->hafas_id = $identifier->identifier;
                    $operator->save();
                }

                $identifier = OperatorIdentifier::where('operator_id', $operator->id)
                    ->where('type', 'motis')
                    ->first();
                if ($identifier) {
                    $operator->motis_id = $identifier->identifier;
                    $operator->motis_source = $identifier->source;
                    $operator->save();
                }
            } catch (\Throwable $e) {
                // Handle any exceptions that may occur during the migration
                // For example, log the error or rethrow it
                \Log::error('Error migrating identifiers for operator ID ' . $operator->id . ': ' . $e->getMessage());
            }
        }
    }
};
