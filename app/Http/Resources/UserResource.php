<?php

namespace App\Http\Resources;

use App\Http\Controllers\Backend\User\ProfilePictureController;
use App\Models\User;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 *     title="User",
 *     description="User model",
 *     required={"id", "displayName", "username", "profilePicture", "trainDistance", "trainDuration", "points", "mastodonUrl", "privateProfile", "pointsEnabled", "userInvisibleToMe", "muted", "blocked", "following", "followPending", "followedBy", "preventIndex", "bio", "profileLinks"},
 *
 *     @OA\Property(property="id",description="ID",type="integer",example=1),
 *     @OA\Property(property="displayName",description="Display name of the user",example="Gertrud"),
 *     @OA\Property(property="username",description="username of user",example="Gertrud123"),
 *     @OA\Property(property="profilePicture",description="URL of the profile picture of the user",example="https://traewelling.de/@Gertrud123/picture"),
 *     @OA\Property(property="trainDistance",  description="distance travelled by train in meters",  type="integer",  example=12345),
 *     @OA\Property(property="trainDuration",  description="duration travelled by train in minutes",  type="integer",  example=6),
 *     @OA\Property(property="points",  description="Current points of the last 7 days",  type="integer",  example=300),
 *     @OA\Property(property="mastodonUrl",  description="URL to the Mastodon profile of the user",  nullable=true,  example="https://chaos.social/@traewelling"),
 *     @OA\Property(property="privateProfile",  description="is this profile set to private?",  type="boolean",  example=false),
 *     @OA\Property(property="points_enabled",  description="Does this profile allow points? Only offer the UI to show points at any status if this setting is set to true. If set to false, the points will always be displayed as 0",  type="boolean",  example=true),
 *     @OA\Property(property="likes_enabled",  description="Does this profile allow likes? Only offer the UI to like any status if this setting is set to true. If set to false, the likes API will return 403.",  type="boolean",  example=true),
 *     @OA\Property(property="pointsEnabled",  description="Does this profile allow points? Only offer the UI to show points at any status if this setting is set to true. If set to false, the points will always be displayed as 0",  type="boolean",  example=true),
 *     @OA\Property(property="userInvisibleToMe",  description="Can the currently authenticated user see the statuses of this user?",  type="boolean",  example=false),
 *     @OA\Property(property="muted",  description="Is this user muted by the currently authenticated user?",  type="boolean",  example=false),
 *     @OA\Property(property="blocked",  description="Is this user blocked by the currently authenticated user?",  type="boolean",  example=false),
 *     @OA\Property(property="following",  description="Does the currently authenticated user follow this user?",  type="boolean",  example=false),
 *     @OA\Property(property="followPending",  description="Is there a currently pending follow request?",  type="boolean",  example=false),
 *     @OA\Property(property="followedBy",  description="Is the user following you?",  type="boolean",  example=false),
 *     @OA\Property(property="preventIndex",  description="Did the user choose to prevent search engines from indexing their profile?",  type="boolean",  example=false),
 *     @OA\Property(property="bio",  description="Bio of the user",  type="string",  example="Hi there! I am Gertrud!"),
 *     @OA\Property(property="profileLinks",  description="Profile links of the user",  type="array",  @OA\Items(ref="#/components/schemas/ProfileLinkResource")),
 * )
 */
class UserResource extends JsonResource
{
    public function toArray($request): array
    {
        /** @var User $this */
        $pointsEnabled = $request->user()?->points_enabled ?? true;

        return [
            'id' => (int) $this->id,
            'displayName' => (string) $this->name,
            'username' => (string) $this->username,
            'profilePicture' => ProfilePictureController::getUrlForUserId($this->id),
            'trainDistance' => (float) $this->train_distance, // @deprecated: remove after 2024-08
            'totalDistance' => (float) $this->train_distance,
            'trainDuration' => (int) $this->train_duration, // @deprecated: remove after 2024-08
            'totalDuration' => (int) $this->train_duration,
            'points' => (int) $pointsEnabled ? $this->points : 0,
            'mastodonUrl' => $this->mastodonUrl ?? null,
            'privateProfile' => (bool) $this->private_profile,
            'preventIndex' => $this->prevent_index,
            'likes_enabled' => $this->likes_enabled,
            'pointsEnabled' => $this->points_enabled,
            'userInvisibleToMe' => (bool) $request->user()?->cannot('view', User::find($this->id)),
            'muted' => (bool) $this->muted,
            'blocked' => (bool) $this->is_blocked_by_auth_user,
            'following' => (bool) $this->following,
            'followPending' => (bool) $this->followPending,
            'followedBy' => (bool) $this->followedBy,
            'bio' => $this->bio,
            'profileLinks' => ProfileLinkResource::collection($this->profileLinks),
        ];
    }
}
