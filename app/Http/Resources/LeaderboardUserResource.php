<?php

namespace App\Http\Resources;

use App\Http\Controllers\Backend\User\ProfilePictureController;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @OA\Schema(
 *      title="LeaderboardUserResource",
 *      @OA\Property(property="id", type="integer", example="1"),
 *      @OA\Property(property="displayName", type="string", example="Gertrud"),
 *      @OA\Property(property="username", type="string", description="username of user", example="Gertrud123"),
 *      @OA\Property(property="profilePicture", type="string", description="URL of the profile picture of the user", example="https://traewelling.de/@Gertrud123/picture"),
 *      @OA\Property(property="totalDuration", type="integer", description="duration travelled in minutes", example=6),
 *      @OA\Property(property="totalDistance", type="integer", description="distance travelled in meters", example=12345),
 *      @OA\Property(property="points", type="integer", description="points of user")
 * )
 */
class LeaderboardUserResource extends JsonResource
{
    public function toArray($request): array {
        return [
            'id'             => (int) $this->user->id,
            'displayName'    => (string) $this->user->name,
            'username'       => (string) $this->user->username,
            'profilePicture' => ProfilePictureController::getUrl($this->user),
            'trainDuration'  => (int) $this->duration, // @deprecated: remove after 2024-08
            'trainDistance'  => (float) $this->distance, // @deprecated: remove after 2024-08
            'totalDuration'  => (int) $this->duration,
            'totalDistance'  => (float) $this->distance,
            'points'         => (int) $this->points
        ];
    }
}
