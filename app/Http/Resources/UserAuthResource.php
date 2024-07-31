<?php

namespace App\Http\Resources;

use App\Http\Controllers\Backend\User\ProfilePictureController;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @OA\Schema(
 *      title="UserAuth",
 *      @OA\Property(property="id", type="integer", example="1"),
 *      @OA\Property(property="displayName", type="string", example="Gertrud"),
 *      @OA\Property(property="username", type="string", example="Gertrud123"),
 *      @OA\Property(property="profilePicture", type="string", example="https://traewelling.de/@Gertrud123/picture"),
 *      @OA\Property(property="totalDistance", type="integer", example="100"),
 *      @OA\Property(property="totalDuration", type="integer", example="100"),
 *      @OA\Property(property="points", type="integer", example="100"),
 *      @OA\Property(property="mastodonUrl", type="string", example="https://mastodon.social/@Gertrud123", nullable=true),
 *      @OA\Property(property="privateProfile", type="boolean", example="false"),
 *      @OA\Property(property="preventIndex", type="boolean", example="false"),
 *      @OA\Property(property="likes_enabled", type="boolean", example="true"),
 *      @OA\Property(property="home", type="object", ref="#/components/schemas/StationResource"),
 *      @OA\Property(property="language", type="string", example="de"),
 *      @OA\Property(property="defaultStatusVisibility", type="integer", example=0),
 *      @OA\Property(property="roles", type="array", @OA\Items(type="string"), example={"admin", "open-beta", "closed-beta"})
 * )
 */
class UserAuthResource extends JsonResource
{
    public function toArray($request): array {
        $pointsEnabled = $request->user()?->points_enabled ?? true;
        return [
            'id'                      => (int) $this->id,
            'displayName'             => (string) $this->name,
            'username'                => (string) $this->username,
            'profilePicture'          => ProfilePictureController::getUrlForUserId($this->id),
            'trainDistance'           => (float) $this->train_distance, // @deprecated: remove after 2024-08
            'totalDistance'           => (float) $this->train_distance,
            'trainDuration'           => (int) $this->train_duration, // @deprecated: remove after 2024-08
            'totalDuration'           => (int) $this->train_duration,
            'points'                  => (int) $pointsEnabled ? $this->points : 0,
            'mastodonUrl'             => $this->mastodonUrl ?? null,
            'privateProfile'          => (bool) $this->private_profile,
            'preventIndex'            => $this->prevent_index,
            'likes_enabled'           => $this->likes_enabled,
            'home'                    => new StationResource($this->home),
            'language'                => $this->language,
            'defaultStatusVisibility' => $this->default_status_visibility,
            'roles'                   => $this->roles->pluck('name')
        ];
    }
}
