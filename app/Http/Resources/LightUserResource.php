<?php

namespace App\Http\Resources;

use App\Models\MastodonServer;
use App\Models\SocialLoginProfile;
use App\Models\User;
use App\Services\ProfilePictureService;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Attributes as OA;

/**
 * @mixin User
 */
#[OA\Schema(
    title: 'LightUser',
    description: 'User model with just basic information',
    required: ['id', 'uuid', 'displayName', 'username', 'profilePicture', 'preventIndex'],
    properties: [
        new OA\Property(property: 'id', type: 'integer', example: 1),
        new OA\Property(property: 'uuid', description: 'UUID', type: 'string', format: 'uuid', example: '00000000-0000-0000-0000-000000000000'),
        new OA\Property(property: 'displayName', type: 'string', example: 'Gertrud'),
        new OA\Property(property: 'username', type: 'string', example: 'Gertrud123'),
        new OA\Property(
            property: 'profilePicture',
            type: 'string',
            example: 'https://traewelling.de/@Gertrud123/picture',
        ),
        new OA\Property(
            property: 'mastodon',
            type: 'object',
            example: ['server' => 'mastodon.social', 'user_id' => 1234567],
        ),
        new OA\Property(property: 'preventIndex', type: 'boolean', example: false),
    ],
)]
class LightUserResource extends JsonResource
{
    public function toArray($request): array
    {
        /** @var User $user */
        $user = $this->resource;
        /** @var SocialLoginProfile|null $socialProfile */
        $socialProfile = $user->socialProfile;
        /** @var MastodonServer|null $mastodonServer */
        $mastodonServer = $socialProfile?->mastodonServer;

        return [
            'id' => (int) $user->id,
            'uuid' => (string) $user->uuid,
            'displayName' => (string) $user->name,
            'username' => (string) $user->username,
            'profilePicture' => resolve(ProfilePictureService::class)->getUrl($user),
            'mastodon' => [
                'server' => $mastodonServer?->domain,
                'user_id' => $socialProfile?->mastodon_id,
            ],
            'mastodonUrl' => null, // TODO: remove after 2026-07 (this is not lightweight enough for a LightResource)
            'preventIndex' => (bool) $user->prevent_index,
        ];
    }
}
