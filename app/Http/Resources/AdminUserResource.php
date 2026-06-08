<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\User;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Attributes as OA;
use Spatie\Permission\Models\Role;

#[OA\Schema(
    title: 'AdminUserResource',
    required: [
        'id', 'username', 'displayName', 'email', 'emailVerifiedAt', 'hasPassword', 'mastodonUrl',
        'lastLogin', 'createdAt', 'trainDistance', 'trainDuration', 'points',
        'roles', 'allRoles', 'mailChanges',
        'privacyPolicyCurrent', 'privacyPolicyFuture', 'privacyPolicyFutureExists',
        'recentStatuses',
    ],
    properties: [
        new OA\Property(property: 'id', type: 'integer'),
        new OA\Property(property: 'username', type: 'string'),
        new OA\Property(property: 'displayName', type: 'string'),
        new OA\Property(property: 'email', type: 'string', nullable: true),
        new OA\Property(property: 'emailVerifiedAt', type: 'string', format: 'date-time', nullable: true),
        new OA\Property(property: 'hasPassword', type: 'boolean'),
        new OA\Property(property: 'mastodonUrl', type: 'string', nullable: true),
        new OA\Property(property: 'lastLogin', type: 'string', format: 'date-time', nullable: true),
        new OA\Property(property: 'createdAt', type: 'string', format: 'date-time'),
        new OA\Property(property: 'trainDistance', type: 'number', description: 'Total distance in metres'),
        new OA\Property(property: 'trainDuration', type: 'number', description: 'Total duration in minutes'),
        new OA\Property(property: 'points', type: 'integer'),
        new OA\Property(property: 'roles', type: 'array', items: new OA\Items(type: 'string')),
        new OA\Property(
            property: 'allRoles',
            type: 'array',
            items: new OA\Items(
                required: ['name', 'permissions'],
                properties: [
                    new OA\Property(property: 'name', type: 'string'),
                    new OA\Property(property: 'permissions', type: 'array', items: new OA\Items(type: 'string')),
                ],
                type: 'object',
            ),
        ),
        new OA\Property(
            property: 'mailChanges',
            type: 'array',
            items: new OA\Items(
                required: ['id', 'oldEmail', 'newEmail', 'createdAt'],
                properties: [
                    new OA\Property(property: 'id', type: 'string'),
                    new OA\Property(property: 'oldEmail', type: 'string'),
                    new OA\Property(property: 'newEmail', type: 'string'),
                    new OA\Property(property: 'createdAt', type: 'string', format: 'date-time', nullable: true),
                ],
                type: 'object',
            ),
        ),
        new OA\Property(property: 'privacyPolicyCurrent', type: 'string', format: 'date-time', nullable: true),
        new OA\Property(property: 'privacyPolicyFuture', type: 'string', format: 'date-time', nullable: true),
        new OA\Property(property: 'privacyPolicyFutureExists', type: 'boolean'),
        new OA\Property(
            property: 'recentStatuses',
            type: 'array',
            items: new OA\Items(ref: AdminStatusResource::class),
        ),
    ],
)]
class AdminUserResource extends JsonResource
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
            'hasPassword' => $this->password !== null,
            'mastodonUrl' => $this->mastodonUrl,
            'lastLogin' => $this->last_login?->toIso8601String(),
            'createdAt' => $this->created_at->toIso8601String(),
            'trainDistance' => (float) $this->train_distance,
            'trainDuration' => (float) $this->train_duration,
            'points' => (int) $this->points,
            'roles' => $this->getRoleNames()->values()->toArray(),
            'allRoles' => Role::with('permissions')->get()->map(fn ($role) => [
                'name' => $role->name,
                'permissions' => $role->permissions->pluck('name')->values()->toArray(),
            ])->values()->toArray(),
            'mailChanges' => $this->whenLoaded('mailChanges', fn () => $this->mailChanges
                ->map(fn ($mc) => [
                    'id' => $mc->id,
                    'oldEmail' => $mc->old_email,
                    'newEmail' => $mc->new_email,
                    'createdAt' => $mc->created_at?->toIso8601String(),
                ])->values()->toArray()),
            'privacyPolicyCurrent' => $this->getAttribute('privacyPolicyCurrent'),
            'privacyPolicyFuture' => $this->getAttribute('privacyPolicyFuture'),
            'privacyPolicyFutureExists' => (bool) $this->getAttribute('privacyPolicyFutureExists'),
            'recentStatuses' => $this->whenLoaded(
                'statuses',
                fn () => AdminStatusResource::collection($this->statuses),
            ),
        ];
    }
}
