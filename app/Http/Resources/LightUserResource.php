<?php

namespace App\Http\Resources;

use App\Http\Controllers\Backend\User\ProfilePictureController;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @OA\Schema(
 *      title="LightUser",
 *      description="User model with just basic information",
 *      @OA\Property(property="id", type="integer", example=1),
 *      @OA\Property(property="displayName", type="string", example="Gertrud"),
 *      @OA\Property(property="username", type="string", example="Gertrud123"),
 *      @OA\Property(property="profilePicture", type="string", example="https://traewelling.de/@Gertrud123/picture"),
 *      @OA\Property(property="mastodonUrl", type="string", example="https://traewelling.social/@Gertrud123"),
 *      @OA\Property(property="preventIndex", type="boolean", example=false)
 * )
 */
class LightUserResource extends JsonResource
{
    public function toArray($request): array {
        return [
            'id'             => (int) $this->id,
            'displayName'    => (string) $this->name,
            'username'       => (string) $this->username,
            'profilePicture' => ProfilePictureController::getUrl($this->resource),
            'mastodonUrl'    => $this->mastodonUrl ?? null,
            'preventIndex'   => (bool) $this->prevent_index,
        ];
    }
}
