<?php

namespace App\Http\Resources;

use App\Models\Operator;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 *     schema="OperatorResource",
 *     required={"id", "identifier", "name"},
 *
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="identifier", type="string", example="db-regio-ag-nord", nullable=true),
 *     @OA\Property(property="name", type="string", example="DB Regio AG Nord")
 * )
 */
class OperatorResource extends JsonResource
{
    public function toArray($request)
    {
        /** @var Operator $this */
        return [
            'id' => $this->id,
            'identifier' => $this->identifiers()->where('type', 'hafas')->first()?->identifier, // TODO: rename to... i don't know, but not identifier
            'name' => $this->name,
        ];
    }
}
