<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\Report;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'ReportResource',
    required: ['id', 'status', 'subject_type', 'subject_id', 'reason', 'description', 'reporter', 'created_at'],
    properties: [
        new OA\Property(property: 'id', type: 'string', format: 'uuid', example: '123e4567-e89b-12d3-a456-426614174000'),
        new OA\Property(property: 'status', type: 'string', example: 'open', enum: ['open', 'waiting', 'closed']),
        new OA\Property(property: 'subject_type', type: 'string', example: 'Status'),
        new OA\Property(property: 'subject_id', type: 'integer', example: 1),
        new OA\Property(property: 'reason', type: 'string', nullable: true, enum: ['inappropriate', 'implausible', 'spam', 'illegal', 'other']),
        new OA\Property(property: 'description', type: 'string', nullable: true),
        new OA\Property(property: 'reporter', ref: '#/components/schemas/LightUserResource', nullable: true),
        new OA\Property(property: 'created_at', type: 'string', format: 'date-time'),
        new OA\Property(
            property: 'activities',
            type: 'array',
            items: new OA\Items(
                properties: [
                    new OA\Property(property: 'id', type: 'integer'),
                    new OA\Property(property: 'description', type: 'string'),
                    new OA\Property(property: 'causer', ref: '#/components/schemas/LightUserResource', nullable: true),
                    new OA\Property(property: 'properties', type: 'object', nullable: true),
                    new OA\Property(property: 'created_at', type: 'string', format: 'date-time'),
                ],
            ),
            nullable: true,
        ),
    ],
)]
class ReportResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        /** @var Report $this */
        return [
            'id' => $this->id,
            'status' => $this->status->value,
            'subject_type' => class_basename($this->subject_type),
            'subject_id' => $this->subject_id,
            'reason' => $this->reason?->value,
            'description' => $this->description,
            'reporter' => new LightUserResource($this->whenLoaded('reporter')),
            'created_at' => $this->created_at,
            'activities' => $this->whenLoaded('activitiesAsSubject', fn () => $this->activitiesAsSubject->map(fn ($activity) => [
                'id' => $activity->id,
                'description' => $activity->description,
                'causer' => $activity->causer ? new LightUserResource($activity->causer) : null,
                'properties' => $activity->properties,
                'created_at' => $activity->created_at,
            ])),
        ];
    }
}
