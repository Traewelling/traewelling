<?php

namespace App\Http\Resources;

use App\Models\User;
use App\Services\ProfilePictureService;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Attributes as OA;

#[OA\Schema(
    title: 'UserProfileSettings',
    required: [
        'uuid',
        'username',
        'displayName',
        'profilePicture',
        'privateProfile',
        'preventIndex',
        'defaultStatusVisibility',
        'privacyHideDays',
        'password',
        'email',
        'emailVerified',
        'profilePictureSet',
        'likesEnabled',
        'pointsEnabled',
        'profileLinks',
        'mapProvider',
        'timezone',
        'experimental',
        'friendCheckin',
        'mastodon',
        'mastodonVisibility',
        'bio',
    ],
    properties: [
        new OA\Property(property: 'uuid', type: 'string', format: 'uuid'),
        new OA\Property(property: 'username', type: 'string', example: 'Gertrud123'),
        new OA\Property(property: 'displayName', type: 'string', example: 'Gertrud'),
        new OA\Property(
            property: 'profilePicture',
            type: 'string',
            example: 'https://traewelling.de/@Gertrud123/picture',
        ),
        new OA\Property(property: 'privateProfile', type: 'boolean', example: false),
        new OA\Property(
            property: 'preventIndex',
            description: 'Did the user choose to prevent search engines from indexing their profile?',
            type: 'boolean',
            example: false,
        ),
        new OA\Property(
            property: 'defaultStatusVisibility',
            ref: '#/components/schemas/StatusVisibility',
        ),
        new OA\Property(
            property: 'privacyHideDays',
            description: 'Number of days to hide the user\'s location history. Null if disabled.',
            type: 'integer',
            example: 1,
            nullable: true,
        ),
        new OA\Property(property: 'password', type: 'boolean', example: true),
        new OA\Property(property: 'email', type: 'string', example: 'gertrud@traewelling.de'),
        new OA\Property(property: 'emailVerified', type: 'boolean', example: true),
        new OA\Property(property: 'profilePictureSet', type: 'boolean', example: true),
        new OA\Property(
            property: 'mastodon',
            type: 'string',
            example: 'https://mastodon.social/@Gertrud123',
        ),
        new OA\Property(
            property: 'mastodonVisibility',
            ref: '#/components/schemas/MastodonVisibility',
        ),
        new OA\Property(property: 'friendCheckin', ref: '#/components/schemas/FriendCheckinSetting'),
        new OA\Property(property: 'likesEnabled', type: 'boolean', example: true),
        new OA\Property(property: 'pointsEnabled', type: 'boolean', example: true),
        new OA\Property(property: 'mapProvider', ref: '#/components/schemas/MapProvider'),
        new OA\Property(property: 'timezone', type: 'string', example: 'Europe/Berlin'),
        new OA\Property(property: 'bio', type: 'string', example: 'Hi there! I am Gertrud!'),
        new OA\Property(
            property: 'profileLinks',
            type: 'array',
            items: new OA\Items(ref: '#/components/schemas/ProfileLinkResource'),
        ),
        new OA\Property(
            property: 'experimental',
            description: 'Experimental features enabled',
            type: 'boolean',
            example: false,
        ),
    ],
)]
class UserProfileSettingsResource extends JsonResource
{
    public function toArray($request): array
    {
        /** @var User $this */
        return [
            'uuid' => $this->uuid,
            'username' => $this->username,
            'displayName' => $this->name,
            'profilePicture' => resolve(ProfilePictureService::class)->getUrlForUserId($this->id),
            'privateProfile' => (bool) $this->private_profile,
            'preventIndex' => (bool) $this->prevent_index,
            'defaultStatusVisibility' => (int) $this->default_status_visibility->value,
            'privacyHideDays' => $this->privacy_hide_days ?: null,
            'password' => (bool) !empty($this->password),
            'email' => $this->email,
            'emailVerified' => !empty($this->email_verified_at),
            'profilePictureSet' => !empty($this->avatar),
            'mastodon' => $this->mastodon_url,
            'mastodonVisibility' => $this->socialProfile->mastodon_visibility->value,
            'friendCheckin' => $this->friend_checkin?->value,
            'likesEnabled' => (bool) $this->likes_enabled,
            'pointsEnabled' => (bool) $this->points_enabled,
            'mapProvider' => $this->mapprovider,
            'timezone' => $this->timezone,
            'experimental' => (bool) $this->hasRole('open-beta'),
            'bio' => $this->bio,
            'profileLinks' => ProfileLinkResource::collection($this->profileLinks),
        ];
    }
}
