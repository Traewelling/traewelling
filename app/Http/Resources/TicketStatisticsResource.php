<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Dto\TicketStatisticsDto;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'TicketStatisticsResource',
    title: 'TicketStatisticsResource',
    description: 'Usage statistics for a single ticket',
    properties: [
        new OA\Property(property: 'tripCount', description: 'Total number of trips assigned to this ticket', type: 'integer', example: 42),
        new OA\Property(property: 'distance', description: 'Total distance of all assigned trips in meters', type: 'integer', example: 123400),
        new OA\Property(property: 'duration', description: 'Total duration of all assigned trips in minutes', type: 'integer', example: 1020),
        new OA\Property(property: 'firstUsed', description: 'Date of the first trip using this ticket (YYYY-MM-DD)', type: 'string', format: 'date', example: '2026-01-03', nullable: true),
        new OA\Property(property: 'lastUsed', description: 'Date of the most recent trip using this ticket (YYYY-MM-DD)', type: 'string', format: 'date', example: '2026-03-14', nullable: true),
        new OA\Property(property: 'costPerTrip', description: 'Ticket price divided by number of trips. Null if no price set.', type: 'number', format: 'float', example: 76.17, nullable: true),
        new OA\Property(property: 'costPerKm', description: 'Ticket price per kilometer. Null if no price set or total distance is zero.', type: 'number', format: 'float', example: 0.26, nullable: true),
        new OA\Property(property: 'costPerHour', description: 'Ticket price per hour of travel. Null if no price set or total duration is zero.', type: 'number', format: 'float', example: 4.48, nullable: true),
        new OA\Property(
            property: 'purposes',
            description: 'Trip counts and distances grouped by travel purpose',
            type: 'array',
            items: new OA\Items(
                properties: [
                    new OA\Property(property: 'reason', description: 'Business value (0=private, 1=business, 2=commute)', type: 'string', example: '2', nullable: true),
                    new OA\Property(property: 'count', type: 'integer', example: 30),
                    new OA\Property(property: 'distance', description: 'Total distance for this purpose in meters', type: 'integer', example: 9000),
                ],
                type: 'object',
            ),
        ),
        new OA\Property(
            property: 'categories',
            description: 'Trip counts and distances grouped by transport category',
            type: 'array',
            items: new OA\Items(
                properties: [
                    new OA\Property(property: 'name', description: 'Transport category (e.g. nationalExpress, tram, bus)', type: 'string', example: 'nationalExpress', nullable: true),
                    new OA\Property(property: 'count', type: 'integer', example: 28),
                    new OA\Property(property: 'distance', description: 'Total distance for this category in meters', type: 'integer', example: 102000),
                ],
                type: 'object',
            ),
        ),
        new OA\Property(
            property: 'operators',
            description: 'Distance grouped by operator, top 10 by distance',
            type: 'array',
            items: new OA\Items(
                properties: [
                    new OA\Property(property: 'name', description: 'Operator name', type: 'string', example: 'DB Fernverkehr', nullable: true),
                    new OA\Property(property: 'count', type: 'integer', example: 28),
                    new OA\Property(property: 'distance', description: 'Total distance for this operator in meters', type: 'integer', example: 102000),
                ],
                type: 'object',
            ),
        ),
    ],
    type: 'object',
)]
class TicketStatisticsResource extends JsonResource
{
    /** @var TicketStatisticsDto */
    public $resource;

    public function __construct(TicketStatisticsDto $dto)
    {
        parent::__construct($dto);
    }

    public function toArray(Request $request): array
    {
        return [
            'tripCount' => $this->resource->tripCount,
            'distance' => $this->resource->distance,
            'duration' => $this->resource->duration,
            'firstUsed' => $this->resource->firstUsed,
            'lastUsed' => $this->resource->lastUsed,
            'costPerTrip' => $this->resource->costPerTrip,
            'costPerKm' => $this->resource->costPerKm,
            'costPerHour' => $this->resource->costPerHour,
            'purposes' => $this->resource->purposes,
            'categories' => $this->resource->categories,
            'operators' => $this->resource->operators,
        ];
    }
}
