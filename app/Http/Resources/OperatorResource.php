<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @OA\Schema(
 *     schema="OperatorResource",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="identifier", type="string", example="db-regio-ag-nord"),
 *     @OA\Property(property="name", type="string", example="DB Regio AG Nord")
 * )
 */
class OperatorResource extends JsonResource
{
    public function toArray($request) {
        return [
            'id'         => $this->id,
            'identifier' => $this->hafas_id, //TODO: rename to... i don't know, but not identifier
            'name'       => $this->name
        ];
    }
}
