<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\OperatorIdentifier;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'OperatorIdentifierResource',
    required: ['type', 'identifier', 'name'],
    properties: [
        new OA\Property(property: 'type', type: 'string', example: 'motis'),
        new OA\Property(property: 'identifier', type: 'string', example: 'de:db-regio-ag'),
        new OA\Property(property: 'name', type: 'string', example: 'DB Regio AG', nullable: true),
    ],
)]
class OperatorIdentifierResource extends JsonResource
{
    public function toArray($request): array
    {
        /** @var OperatorIdentifier $this */
        return [
            'type' => $this->type,
            'identifier' => $this->identifier,
            'name' => $this->name,
        ];
    }
}
