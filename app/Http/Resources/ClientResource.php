<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Model -> OAuthClient
 * @OA\Schema(
 *      title="Client",
 *      @OA\Property(property="id", type="integer", example=1),
 *      @OA\Property(property="name", type="string", example="Träwelling App"),
 *      @OA\Property(property="privacyPolicyUrl", type="string", example="https://traewelling.de/privacy-policy")
 * )
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
