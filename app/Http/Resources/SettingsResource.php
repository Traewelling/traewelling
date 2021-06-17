<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class SettingsResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function toArray($request) {
        return [
            'role'          => $this->role,
            'home'          => $this->home,
            'private'       => $this->private_profile,
            'prevent_index' => $this->prevent_index,
            'dbl'           => $this->always_dbl,
            'language'      => $this->language
        ];
    }
}
