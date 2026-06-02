<?php

declare(strict_types=1);

namespace App\Http\Resources\Contribution;

use App\Models\ContributionHistory;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'ContributionHistory',
    title: 'ContributionHistory',
    description: 'A single contribution history entry',
    required: [
        'id',
        'actionType',
        'entityType',
        'entityId',
        'xpChange',
        'levelBefore',
        'levelAfter',
        'note',
        'createdAt',
    ],
    properties: [
        new OA\Property(
            property: 'id',
            type: 'string',
            format: 'uuid',
            example: '9e3a1b2c-4d5e-6f7a-8b9c-0d1e2f3a4b5c',
        ),
        new OA\Property(property: 'actionType', type: 'string', example: 'event_suggested'),
        new OA\Property(property: 'entityType', type: 'string', example: 'event_suggestion'),
        new OA\Property(property: 'entityId', type: 'integer', example: 42),
        new OA\Property(property: 'xpChange', type: 'integer', example: 5),
        new OA\Property(property: 'levelBefore', type: 'integer', example: 0),
        new OA\Property(property: 'levelAfter', type: 'integer', example: 1),
        new OA\Property(
            property: 'note',
            type: 'string',
            example: 'Event approved: GPN 22',
            nullable: true,
        ),
        new OA\Property(
            property: 'createdAt',
            type: 'string',
            format: 'date-time',
            example: '2026-02-15T12:00:00Z',
        ),
    ],
)]
class ContributionHistoryResource extends JsonResource
{
    public function toArray($request): array
    {
        /** @var ContributionHistory $this */
        return [
            'id' => $this->id,
            'actionType' => $this->action_type->value,
            'entityType' => $this->entity_type,
            'entityId' => $this->entity_id,
            'xpChange' => $this->xp_change,
            'levelBefore' => $this->level_before,
            'levelAfter' => $this->level_after,
            'note' => $this->note,
            'createdAt' => $this->created_at->toIso8601String(),
        ];
    }
}
