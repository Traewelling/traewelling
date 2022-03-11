<?php

namespace App\Http\Resources;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PrivacyPolicyResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     *
     * @return array
     */
    public function toArray($request) {
        return [
            'validFrom' => $this->valid_at,
            'en'        => $this->body_md_en,
            'de'        => $this->body_md_de
        ];
    }
}
