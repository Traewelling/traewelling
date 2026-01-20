<?php

namespace App\Http\Resources;

use App\Http\Controllers\Backend\User\ProfilePictureController;
use App\Models\MastodonServer;
use App\Models\SocialLoginProfile;
use App\Models\User;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @OA\Schema(
 *      title="LightUser",
 *      description="User model with just basic information",
 *      required={"id", "displayName", "username", "profilePicture", "preventIndex"},
 *
 *      @OA\Property(property="id", type="integer", example=1),
 *      @OA\Property(property="displayName", type="string", example="Gertrud"),
 *      @OA\Property(property="username", type="string", example="Gertrud123"),
 *      @OA\Property(property="profilePicture", type="string", example="https://traewelling.de/@Gertrud123/picture"),
 *      @OA\Property(property="mastodon", type="object", example={"server": "mastodon.social", "user_id": 1234567}),
 *      @OA\Property(property="preventIndex", type="boolean", example=false)
 * )
 *
 * @mixin User
 */
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
            'displayName' => (string) $user->name,
            'username' => (string) $user->username,
            'profilePicture' => ProfilePictureController::getUrl($user),
            'mastodon' => [
                'server' => $mastodonServer?->domain,
                'user_id' => $socialProfile?->mastodon_id,
            ],
            'mastodonUrl' => null, // TODO: remove after 2026-07 (this is not lightweight enough for a LightResource)
            'preventIndex' => (bool) $user->prevent_index,
        ];
    }
}
