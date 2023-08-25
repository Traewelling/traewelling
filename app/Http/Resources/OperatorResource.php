<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class OperatorResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'identifier'    => $this->hafas_id,
            'name'  => $this->name
        ];
    }
}
