<?php

namespace App\Http\Resources;

use App\Http\Controllers\Backend\User\ProfilePictureController;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @OA\Schema(
 *      title="LeaderboardUserResource",
*      @OA\Property(property="user", ref="#/components/schemas/LightUserResource"),
 *      @OA\Property(property="totalDuration", type="integer", description="duration travelled in minutes", example=6),
 *      @OA\Property(property="totalDistance", type="integer", description="distance travelled in meters", example=12345),
 *      @OA\Property(property="points", type="integer", description="points of user")
 * )
 */
class LeaderboardUserResource extends JsonResource
{
    public function toArray($request): array {
        return [
            'id'             => (int) $this->user->id, // @deprecated: remove after 2024-08
            'displayName'    => (string) $this->user->name, // @deprecated: remove after 2024-08
            'username'       => (string) $this->user->username, // @deprecated: remove after 2024-08
            'profilePicture' => ProfilePictureController::getUrl($this->user), // @deprecated: remove after 2024-08
            'trainDuration'  => (int) $this->duration, // @deprecated: remove after 2024-08
            'trainDistance'  => (float) $this->distance, // @deprecated: remove after 2024-08
            'user'           => new LightUserResource($this->user),
            'totalDuration'  => (int) $this->duration,
            'totalDistance'  => (float) $this->distance,
            'points'         => (int) $this->points,
        ];
    }
}
