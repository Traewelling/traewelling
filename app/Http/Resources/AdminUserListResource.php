<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\User;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Attributes as OA;

#[OA\Schema(
    title: 'AdminUserListItem',
    required: ['id', 'username', 'displayName', 'email', 'emailVerifiedAt', 'mastodonUrl', 'lastLogin', 'createdAt'],
    properties: [
        new OA\Property(property: 'id', type: 'integer'),
        new OA\Property(property: 'username', type: 'string'),
        new OA\Property(property: 'displayName', type: 'string'),
        new OA\Property(property: 'email', type: 'string', nullable: true),
        new OA\Property(property: 'emailVerifiedAt', type: 'string', format: 'date-time', nullable: true),
        new OA\Property(property: 'mastodonUrl', type: 'string', nullable: true),
        new OA\Property(property: 'lastLogin', type: 'string', format: 'date-time', nullable: true),
        new OA\Property(property: 'createdAt', type: 'string', format: 'date-time'),
    ],
)]
class AdminUserListResource extends JsonResource
{
    public function toArray($request): array
    {
        /** @var User $this */
        return [
            'id' => (int) $this->id,
            'username' => $this->username,
            'displayName' => $this->name,
            'email' => $this->email,
            'emailVerifiedAt' => $this->email_verified_at?->toIso8601String(),
            'mastodonUrl' => $this->mastodonUrl,
            'lastLogin' => $this->last_login?->toIso8601String(),
            'createdAt' => $this->created_at->toIso8601String(),
        ];
    }
}
