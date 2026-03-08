<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Route segments were incorrectly created using real-time departure/arrival times
 * instead of planned times. This migration nullifies route_segment_id on stopovers
 * where the assigned segment's duration does not match the planned duration of the
 * stopover pair (±10% tolerance).
 */
return new class() extends Migration
{
    private const TOLERANCE = 0.10;

    private const CHUNK_SIZE = 200;

    public function up(): void
    {
        $toNullify = [];

        // Collect distinct trip_ids that have at least one segment-assigned stopover,
        // and process them in chunks to avoid the MySQL placeholder limit.
        DB::table('train_stopovers')
            ->whereNotNull('route_segment_id')
            ->distinct()
            ->orderBy('trip_id')
            ->pluck('trip_id')
            ->chunk(self::CHUNK_SIZE)
            ->each(function ($tripIds) use (&$toNullify): void {
                $tripIds = $tripIds->all();

                // All stopovers for these trips, in timetable order.
                $allRows = DB::table('train_stopovers')
                    ->whereIn('trip_id', $tripIds)
                    ->orderBy('trip_id')
                    ->orderBy('arrival_planned')
                    ->orderBy('departure_planned')
                    ->get(['id', 'trip_id', 'route_segment_id', 'arrival_planned', 'departure_planned']);

                $allByTrip = [];
                foreach ($allRows as $row) {
                    $allByTrip[$row->trip_id][] = $row;
                }

                $segmentIds = $allRows->pluck('route_segment_id')->filter()->unique()->values()->all();

                if (empty($segmentIds)) {
                    return;
                }

                $segments = DB::table('route_segments')
                    ->whereIn('id', $segmentIds)
                    ->get(['id', 'duration'])
                    ->keyBy('id');

                foreach ($allByTrip as $stopRows) {
                    foreach ($stopRows as $i => $stop) {
                        if ($stop->route_segment_id === null) {
                            continue;
                        }

                        $segment = $segments->get($stop->route_segment_id);
                        if (!$segment || $segment->duration === null) {
                            continue;
                        }

                        $nextStop = $stopRows[$i + 1] ?? null;
                        if ($nextStop === null) {
                            continue;
                        }

                        $startTime = $stop->departure_planned ?? $stop->arrival_planned;
                        $endTime = $nextStop->arrival_planned ?? $nextStop->departure_planned;

                        if ($startTime === null || $endTime === null) {
                            continue;
                        }

                        $plannedDuration = (int) round(strtotime($endTime) - strtotime($startTime));

                        if ($plannedDuration <= 0) {
                            continue;
                        }

                        $lower = $plannedDuration * (1 - self::TOLERANCE);
                        $upper = $plannedDuration * (1 + self::TOLERANCE);

                        if ($segment->duration < $lower || $segment->duration > $upper) {
                            $toNullify[] = $stop->id;
                            Log::info('Cleanup: nullifying route_segment_id on stopover', [
                                'stopover_id' => $stop->id,
                                'segment_id' => $stop->route_segment_id,
                                'segment_duration' => $segment->duration,
                                'planned_duration' => $plannedDuration,
                            ]);
                        }
                    }
                }
            });

        foreach (array_chunk($toNullify, self::CHUNK_SIZE) as $chunk) {
            DB::table('train_stopovers')
                ->whereIn('id', $chunk)
                ->update(['route_segment_id' => null]);
        }

        Log::info('Cleanup: stopover segment assignments nullified', ['count' => count($toNullify)]);
    }

    public function down(): void
    {
        // This migration is irreversible — data cannot be restored after cleanup.
    }
};
