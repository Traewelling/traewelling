<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

// Deduplicates route_segments where (from_station_id, to_station_id, polyline_hash)
// has more than one row. path_type is ignored as it has been nullified in 000004.
// Winner selection priority:
//   1. Has custom_waypoints (manually curated)
//   2. Most references in train_stopovers
//   3. Oldest record (lowest UUID = earliest UUIDv7 timestamp)
//
// Each duplicate group is processed in its own small transaction to keep lock
// duration minimal. The table remains usable throughout.
return new class() extends Migration
{
    private const GROUP_BATCH_SIZE = 100;

    public function up(): void
    {
        do {
            $groups = DB::table('route_segments')
                ->selectRaw('from_station_id, to_station_id, polyline_hash')
                ->whereNotNull('polyline_hash')
                ->groupBy(['from_station_id', 'to_station_id', 'polyline_hash'])
                ->havingRaw('COUNT(*) > 1')
                ->orderBy('from_station_id')
                ->orderBy('to_station_id')
                ->limit(self::GROUP_BATCH_SIZE)
                ->get();

            foreach ($groups as $group) {
                $this->deduplicateGroup($group);
            }

            // Re-query from offset 0 after each batch: deletions shrink the result set
            // so a fixed offset would skip groups.
        } while ($groups->isNotEmpty());
    }

    private function deduplicateGroup(object $group): void
    {
        DB::transaction(function () use ($group): void {
            $segments = DB::table('route_segments')
                ->leftJoin(
                    'train_stopovers',
                    'route_segments.id',
                    '=',
                    'train_stopovers.route_segment_id'
                )
                ->select([
                    'route_segments.id',
                    DB::raw('COUNT(train_stopovers.id) AS ref_count'),
                ])
                ->where('route_segments.from_station_id', $group->from_station_id)
                ->where('route_segments.to_station_id', $group->to_station_id)
                ->where('route_segments.polyline_hash', $group->polyline_hash)
                ->groupBy(['route_segments.id', 'route_segments.custom_waypoints'])
                // Winner priority: custom_waypoints → most refs → oldest UUID
                ->orderByRaw('route_segments.custom_waypoints IS NOT NULL DESC')
                ->orderByDesc('ref_count')
                ->orderBy('route_segments.id')
                ->get();

            if ($segments->count() < 2) {
                return;
            }

            $winnerId = $segments->first()->id;
            $loserIds = $segments->skip(1)->pluck('id')->all();

            // Reassign all stopover references from losers to winner
            DB::table('train_stopovers')
                ->whereIn('route_segment_id', $loserIds)
                ->update(['route_segment_id' => $winnerId]);

            // Delete the now-unreferenced duplicate segments
            DB::table('route_segments')
                ->whereIn('id', $loserIds)
                ->delete();
        });
    }
};
