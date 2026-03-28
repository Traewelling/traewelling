<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::table('activity_log', function (Blueprint $table): void {
            $table->json('attribute_changes')->nullable()->after('properties');
        });

        // Migrate existing attribute data from properties into attribute_changes
        DB::table('activity_log')
            ->whereNotNull('properties')
            ->lazyById()
            ->each(function (object $row): void {
                $properties = json_decode($row->properties, true);
                if (!is_array($properties)) {
                    return;
                }

                $attributeChanges = [];
                if (isset($properties['attributes'])) {
                    $attributeChanges['attributes'] = $properties['attributes'];
                    unset($properties['attributes']);
                }
                if (isset($properties['old'])) {
                    $attributeChanges['old'] = $properties['old'];
                    unset($properties['old']);
                }

                if ($attributeChanges !== []) {
                    DB::table('activity_log')->where('id', $row->id)->update([
                        'attribute_changes' => json_encode($attributeChanges),
                        'properties' => json_encode($properties),
                    ]);
                }
            });

        Schema::table('activity_log', function (Blueprint $table): void {
            $table->dropColumn('batch_uuid');
        });
    }

    public function down(): void
    {
        Schema::table('activity_log', function (Blueprint $table): void {
            $table->char('batch_uuid', 36)->nullable()->after('properties');
        });

        // Reverse: move attribute_changes back into properties
        DB::table('activity_log')
            ->whereNotNull('attribute_changes')
            ->lazyById()
            ->each(function (object $row): void {
                $attributeChanges = json_decode($row->attribute_changes, true);
                $properties = json_decode($row->properties, true) ?? [];
                if (is_array($attributeChanges)) {
                    $properties = array_merge($properties, $attributeChanges);
                }
                DB::table('activity_log')->where('id', $row->id)->update([
                    'properties' => json_encode($properties),
                ]);
            });

        Schema::table('activity_log', function (Blueprint $table): void {
            $table->dropColumn('attribute_changes');
        });
    }
};
