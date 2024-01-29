<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Model -> OAuthClient
 */
class ClientResource extends JsonResource
{
    public function toArray($request) {
        return [
            'id'               => $this->id,
            'name'             => $this->name,
            'privacyPolicyUrl' => $this->privacy_policy_url,
        ];
    }
}
