<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Attributes as OA;

#[OA\Schema(
    title: 'Area',
    required: ['name', 'default', 'adminLevel'],
    properties: [
        new OA\Property(property: 'name', type: 'string', example: 'Karlsruhe'),
        new OA\Property(property: 'default', type: 'boolean', example: true),
        new OA\Property(property: 'adminLevel', type: 'integer', example: '1'),
    ],
)]
class AreaResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $default = false;
        if (isset($this->pivot)) {
            $default = (bool) $this->pivot?->default;
        } elseif (isset($this->default)) {
            $default = (bool) $this->default;
        }

        return [
            'name' => $this->name,
            'default' => $default,
            'adminLevel' => $this->adminLevel,
        ];
    }
}
