<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @OA\Schema(
 *     title="Area",
 *     @OA\Property(property="name", type="string", example="Karlsruhe"),
 *     @OA\Property(property="default", type="boolean", example="true"),
 *     @OA\Property(property="adminLevel", type="integer", example="1"),
 * )
 */
class AreaResource extends JsonResource
{
    public function toArray(Request $request): array {
        return [
            'name'       => $this->name,
            'default'    => $this->pivot->default,
            'adminLevel' => $this->adminLevel,
        ];
    }
}
