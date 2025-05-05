<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 *     title="User",
 *     description="User model",
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
 *     @OA\Property(property="following",  description="Does the currently authenticated user follow this user?",  type="boolean",  example=false),
 *     @OA\Property(property="followPending",  description="Is there a currently pending follow request?",  type="boolean",  example=false),
 *     @OA\Property(property="followedBy",  description="Is the user following you?",  type="boolean",  example=false),
 *     @OA\Property(property="preventIndex",  description="Did the user choose to prevent search engines from indexing their profile?",  type="boolean",  example=false)
 * )
 */
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
    public function toArray($request): UserBaseResource
    {
        return new UserBaseResource($this);
    }
}
