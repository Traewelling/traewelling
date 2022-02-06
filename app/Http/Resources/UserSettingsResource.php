<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserSettingsResource extends JsonResource
{
    protected bool $UserSettingsResource = true;

    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     *
     * @return UserBaseResource
     */
    public function toArray($request): UserBaseResource {
        return new UserBaseResource($this);
    }
}
