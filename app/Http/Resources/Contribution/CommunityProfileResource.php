<?php

declare(strict_types=1);

namespace App\Http\Resources\Contribution;

use App\Models\User;
use App\Services\Contribution\ContributionLevelService;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'CommunityProfile',
    title: 'CommunityProfile',
    description: 'Community contribution profile data',
    required: ['xp', 'level', 'nextLevelXP', 'progressPercent'],
    properties: [
        new OA\Property(
            property: 'xp',
            description: 'Total contribution XP earned',
            type: 'integer',
            example: 75,
        ),
        new OA\Property(
            property: 'level',
            description: 'Current contribution level',
            type: 'integer',
            example: 1,
        ),
        new OA\Property(
            property: 'nextLevelXP',
            description: 'XP required for next level',
            type: 'integer',
            example: 150,
        ),
        new OA\Property(
            property: 'progressPercent',
            description: 'Progress percentage to next level',
            type: 'number',
            format: 'float',
            example: 25.0,
        ),
    ],
)]
class CommunityProfileResource extends JsonResource
{
    public function toArray($request): array
    {
        /** @var User $this */
        return [
            'xp' => (int) $this->contribution_xp,
            'level' => (int) $this->contribution_level,
            'nextLevelXP' => ContributionLevelService::getNextLevelXP($this->contribution_xp),
            'progressPercent' => ContributionLevelService::getProgressToNextLevel(
                $this->contribution_xp,
                $this->contribution_level
            ),
        ];
    }
}
