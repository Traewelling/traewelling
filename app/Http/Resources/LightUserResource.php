<?php

namespace App\Http\Resources;

use App\Http\Controllers\Backend\User\ProfilePictureController;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 *      title="LightUser",
 *      description="User model with just basic information",
 *      required={"id", "displayName", "username", "profilePicture", "preventIndex"},
 *      @OA\Property(property="id", type="integer", example=1),
 *      @OA\Property(property="displayName", type="string", example="Gertrud"),
 *      @OA\Property(property="username", type="string", example="Gertrud123"),
 *      @OA\Property(property="profilePicture", type="string", example="https://traewelling.de/@Gertrud123/picture"),
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
            'mastodonUrl'    => null, // TODO: remove after 2026-07 (this is not lightweight enough for a LightResource)
            'preventIndex'   => (bool) $this->prevent_index,
        ];
    }
}
