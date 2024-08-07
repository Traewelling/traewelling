<?php

namespace App\Http\Resources;

use App\Http\Controllers\Backend\User\ProfilePictureController;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @OA\Schema(
 *      title="UserProfileSettings",
 *      @OA\Property(property="username",                   type="string",  example="Gertrud123"),
 *      @OA\Property(property="displayName",                type="string",  example="Gertrud"),
 *      @OA\Property(property="profilePicture",             type="string",  example="https://traewelling.de/@Gertrud123/picture"),
 *      @OA\Property(property="privateProfile",             type="boolean", example=false),
 *      @OA\Property(property="preventIndex",               type="boolean", example=false,                                          description="Did the user choose to prevent search engines from indexing their profile?"),
 *      @OA\Property(property="defaultStatusVisibility",    ref="#/components/schemas/StatusVisibility"),
 *      @OA\Property(property="privacyHideDays",            type="integer", example=1,                                              description="Number of days to hide the user's location history"),
 *      @OA\Property(property="password",                   type="boolean", example=true),
 *      @OA\Property(property="email",                      type="string",  example="gertrud@traewelling.de"),
 *      @OA\Property(property="emailVerified",              type="boolean", example=true),
 *      @OA\Property(property="profilePictureSet",          type="boolean", example=true),
 *      @OA\Property(property="mastodon",                   type="string",  example="https://mastodon.social/@Gertrud123"),
 *      @OA\Property(property="mastodonVisibility",         ref="#/components/schemas/MastodonVisibility"),
 *      @OA\Property(property="friendCheckin",              ref="#/components/schemas/FriendCheckinSetting"),
 *      @OA\Property(property="likesEnabled",               type="boolean", example=true),
 *      @OA\Property(property="pointsEnabled",              type="boolean", example=true),
 * )
 */
class UserProfileSettingsResource extends JsonResource
{
    public function toArray($request): array {
        return [
            'username'                => $this->username,
            'displayName'             => $this->name,
            'profilePicture'          => ProfilePictureController::getUrlForUserId($this->id),
            'privateProfile'          => (bool) $this->private_profile,
            'preventIndex'            => (bool) $this->prevent_index,
            'defaultStatusVisibility' => (int) $this->default_status_visibility->value,
            'privacyHideDays'         => (int) $this->privacy_hide_days,
            'password'                => (bool) $this->password,
            'email'                   => $this->email,
            'emailVerified'           => !empty($this->email_verified_at),
            'profilePictureSet'       => !empty($this->avatar),
            'mastodon'                => $this->mastodon_url,
            'mastodonVisibility'      => $this->socialProfile->mastodon_visibility->value,
            'friendCheckin'           => $this->friend_checkin?->value,
            'likesEnabled'            => (bool) $this->likes_enabled,
            'pointsEnabled'           => (bool) $this->points_enabled,
        ];
    }
}
