<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class StatusTagResource extends JsonResource
{

    public function toArray($request): array {
        return [
            'key'        => $this->key,
            'value'      => $this->value,
            'visibility' => $this->visibility->value,
        ];
    }
}
