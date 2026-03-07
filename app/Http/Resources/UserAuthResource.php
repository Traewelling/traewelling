<?php

namespace App\Http\Resources;

use App\Http\Controllers\Backend\User\ProfilePictureController;
use App\Models\User;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Attributes as OA;

#[OA\Schema(
    title: 'UserAuth',
    required: [
        'id',
        'displayName',
        'username',
        'profilePicture',
        'totalDistance',
        'totalDuration',
        'points',
        'mastodonUrl',
        'privateProfile',
        'preventIndex',
        'likes_enabled',
        'mapProvider',
        'home',
        'language',
        'defaultStatusVisibility',
        'roles',
    ],
    properties: [
        new OA\Property(property: 'id', type: 'integer', example: '1'),
        new OA\Property(property: 'uuid', description: 'UUID', type: 'string', format: 'uuid', example: '00000000-0000-0000-0000-000000000001'),
        new OA\Property(property: 'displayName', type: 'string', example: 'Gertrud'),
        new OA\Property(property: 'username', type: 'string', example: 'Gertrud123'),
        new OA\Property(
            property: 'profilePicture',
            type: 'string',
            example: 'https://traewelling.de/@Gertrud123/picture',
        ),
        new OA\Property(property: 'totalDistance', type: 'integer', example: '100'),
        new OA\Property(property: 'totalDuration', type: 'integer', example: '100'),
        new OA\Property(property: 'points', type: 'integer', example: '100'),
        new OA\Property(
            property: 'mastodonUrl',
            type: 'string',
            example: 'https://mastodon.social/@Gertrud123',
            nullable: true,
        ),
        new OA\Property(property: 'privateProfile', type: 'boolean', example: false),
        new OA\Property(property: 'preventIndex', type: 'boolean', example: false),
        new OA\Property(property: 'likes_enabled', type: 'boolean', example: true),
        new OA\Property(property: 'pointsEnabled', type: 'boolean', example: true),
        new OA\Property(property: 'mapProvider', type: 'string', example: 'default'),
        new OA\Property(
            property: 'home',
            ref: '#/components/schemas/StationResource',
            type: 'object',
        ),
        new OA\Property(property: 'language', type: 'string', example: 'de'),
        new OA\Property(property: 'defaultStatusVisibility', type: 'integer', example: 0),
        new OA\Property(
            property: 'roles',
            type: 'array',
            items: new OA\Items(type: 'string'),
            example: ['admin', 'open-beta', 'closed-beta'],
        ),
    ],
)]
class UserAuthResource extends JsonResource
{
    public function toArray($request): array
    {
        $pointsEnabled = $request->user()?->points_enabled ?? true;

        /** @var User $this */
        return [
            'id' => (int) $this->id,
            'uuid' => (string) $this->uuid,
            'displayName' => (string) $this->name,
            'username' => (string) $this->username,
            'profilePicture' => ProfilePictureController::getUrlForUserId($this->id),
            'totalDistance' => (float) $this->train_distance,
            'totalDuration' => (int) $this->train_duration,
            'points' => (int) $pointsEnabled ? $this->points : 0,
            'mastodonUrl' => $this->mastodonUrl ?? null,
            'privateProfile' => (bool) $this->private_profile,
            'preventIndex' => $this->prevent_index,
            'likes_enabled' => $this->likes_enabled,
            'pointsEnabled' => $pointsEnabled,
            'mapProvider' => $this->mapprovider ?? 'default',
            'home' => new StationResource($this->home),
            'language' => $this->language,
            'defaultStatusVisibility' => $this->default_status_visibility,
            'roles' => $this->roles->pluck('name'),
        ];
    }
}
