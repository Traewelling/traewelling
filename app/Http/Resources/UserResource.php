<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    protected bool $UserResource = true;

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
