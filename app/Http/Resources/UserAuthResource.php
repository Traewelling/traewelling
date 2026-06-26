<?php

namespace App\Http\Resources;

use App\Models\Checkin;
use App\Models\User;
use App\Services\ProfilePictureService;
use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Attributes as OA;

#[OA\Schema(
    title: 'UserAuth',
    required: [
        'id',
        'uuid',
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
        'pointsEnabled',
        'mapProvider',
        'home',
        'language',
        'defaultStatusVisibility',
        'roles',
        'recentGdprExport',
    ],
    properties: [
        new OA\Property(property: 'id', type: 'integer', example: '1'),
        new OA\Property(property: 'uuid', description: 'UUID', type: 'string', format: 'uuid', example: '00000000-0000-0000-0000-000000000000'),
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
        new OA\Property(property: 'recentGdprExport', type: 'string', format: 'date-time', example: '2024-01-01T00:00:00Z', nullable: true),
    ],
)]
class UserAuthResource extends JsonResource
{
    private function getCheckinStats(): object
    {
        return Checkin::where('user_id', $this->id)
            ->selectRaw('SUM(distance) as total_distance, SUM(duration) as total_duration, SUM(CASE WHEN departure >= ? THEN points ELSE 0 END) as recent_points', [
                Carbon::now()->subDays(7)->format('Y-m-d H:i:s'),
            ])
            ->first();
    }

    public function toArray($request): array
    {
        $pointsEnabled = $request->user()?->points_enabled ?? true;
        $stats = $this->getCheckinStats();

        /** @var User $this */
        return [
            'id' => (int) $this->id,
            'uuid' => (string) $this->uuid,
            'displayName' => (string) $this->name,
            'username' => (string) $this->username,
            'profilePicture' => resolve(ProfilePictureService::class)->getUrlForUserId($this->id),
            'totalDistance' => (float) $stats->total_distance,
            'totalDuration' => (int) $stats->total_duration,
            'points' => (int) ($pointsEnabled ? $stats->recent_points : 0),
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
            'recentGdprExport' => $this->recent_gdpr_export?->toIso8601String(),
        ];
    }
}
