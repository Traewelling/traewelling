<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\DB;
use OpenApi\Attributes as OA;

#[OA\Schema(
    title: 'TicketResource',
    description: 'A transit ticket / Fahrkarte',
    required: ['id', 'name', 'validFrom', 'validUntil', 'price', 'currency', 'createdAt', 'tripCount', 'totalDistance', 'totalDuration'],
    properties: [
        new OA\Property(
            property: 'id',
            description: 'UUID of the ticket',
            type: 'string',
            format: 'uuid',
            example: '00000000-0000-0000-0000-000000000000',
        ),
        new OA\Property(
            property: 'name',
            description: 'User-defined name of the ticket',
            type: 'string',
            example: 'My BahnCard 100',
        ),
        new OA\Property(
            property: 'validFrom',
            description: 'Start of validity period (ISO 8601 date)',
            type: 'string',
            format: 'date',
            example: '2026-01-01',
            nullable: true,
        ),
        new OA\Property(
            property: 'validUntil',
            description: 'End of validity period (ISO 8601 date)',
            type: 'string',
            format: 'date',
            example: '2026-12-31',
            nullable: true,
        ),
        new OA\Property(
            property: 'price',
            description: 'Price of the ticket',
            type: 'number',
            format: 'float',
            example: 3199.00,
            nullable: true,
        ),
        new OA\Property(
            property: 'currency',
            description: 'Currency of the price (free-form, e.g. EUR, CHF)',
            type: 'string',
            example: 'EUR',
            nullable: true,
        ),
        new OA\Property(
            property: 'createdAt',
            description: 'ISO 8601 timestamp of creation',
            type: 'string',
            format: 'date-time',
            example: '2026-03-01T00:00:00Z',
        ),
        new OA\Property(property: 'tripCount', description: 'Number of trips assigned to this ticket', type: 'integer', example: 42),
        new OA\Property(property: 'totalDistance', description: 'Total distance of all trips assigned to this ticket in meters', type: 'integer', example: 12340),
        new OA\Property(property: 'totalDuration', description: 'Total duration of all trips assigned to this ticket in minutes', type: 'integer', example: 1020),
    ],
    type: 'object',
)]
class TicketResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'validFrom' => $this->valid_from?->toDateString(),
            'validUntil' => $this->valid_until?->toDateString(),
            'price' => $this->price !== null ? (float) $this->price : null,
            'currency' => $this->currency,
            'createdAt' => $this->created_at->toIso8601String(),
            'tripCount' => $this->resolveAggregate('trip_count'),
            'totalDistance' => $this->resolveAggregate('total_distance'),
            'totalDuration' => $this->resolveAggregate('total_duration'),
        ];
    }

    private function resolveAggregate(string $attribute): int
    {
        if (isset($this->resource->$attribute)) {
            return (int) $this->resource->$attribute;
        }

        $stats = DB::table('statuses')
            ->leftJoin('train_checkins', 'train_checkins.status_id', '=', 'statuses.id')
            ->where('statuses.ticket_id', $this->id)
            ->selectRaw('COUNT(train_checkins.id) AS trip_count, COALESCE(SUM(train_checkins.distance), 0) AS total_distance, COALESCE(SUM(train_checkins.duration), 0) AS total_duration')
            ->first();

        $this->resource->trip_count = (int) ($stats?->trip_count ?? 0);
        $this->resource->total_distance = (int) ($stats?->total_distance ?? 0);
        $this->resource->total_duration = (int) ($stats?->total_duration ?? 0);

        return (int) $this->resource->$attribute;
    }
}
