<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\User;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Attributes as OA;
use Spatie\Activitylog\Models\Activity;

#[OA\Schema(
    title: 'ActivityLog',
    required: ['id', 'causer', 'description', 'subjectType', 'subjectFullType', 'subjectId', 'changes', 'createdAt'],
    properties: [
        new OA\Property(property: 'id', type: 'integer'),
        new OA\Property(
            property: 'causer',
            type: 'object',
            required: ['id', 'name', 'username'],
            nullable: true,
            properties: [
                new OA\Property(property: 'id', type: 'integer'),
                new OA\Property(property: 'name', type: 'string'),
                new OA\Property(property: 'username', type: 'string'),
            ],
        ),
        new OA\Property(property: 'description', type: 'string'),
        new OA\Property(property: 'subjectType', type: 'string', nullable: true),
        new OA\Property(property: 'subjectFullType', type: 'string', nullable: true),
        new OA\Property(property: 'subjectId', type: 'integer', nullable: true),
        new OA\Property(
            property: 'changes',
            type: 'object',
            required: ['old', 'attributes'],
            properties: [
                new OA\Property(property: 'old', type: 'object', additionalProperties: new OA\AdditionalProperties(nullable: true)),
                new OA\Property(property: 'attributes', type: 'object', additionalProperties: new OA\AdditionalProperties(nullable: true)),
            ],
        ),
        new OA\Property(property: 'createdAt', type: 'string', format: 'date-time'),
    ],
)]
class ActivityLogResource extends JsonResource
{
    public function toArray($request): array
    {
        /** @var Activity $this */
        $changes = $this->attribute_changes?->isNotEmpty()
            ? $this->attribute_changes->toArray()
            : ($this->properties?->toArray() ?? []);
        $causer = $this->causer instanceof User ? $this->causer : null;

        return [
            'id' => $this->id,
            'causer' => $causer ? [
                'id' => $causer->id,
                'name' => $causer->name,
                'username' => $causer->username,
            ] : null,
            'description' => $this->description,
            'subjectType' => $this->subject_type ? class_basename($this->subject_type) : null,
            'subjectFullType' => $this->subject_type,
            'subjectId' => $this->subject_id !== null ? (int) $this->subject_id : null,
            'changes' => [
                'old' => $changes['old'] ?? [],
                'attributes' => $changes['attributes'] ?? [],
            ],
            'createdAt' => $this->created_at->toIso8601String(),
        ];
    }
}
