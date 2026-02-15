<?php

declare(strict_types=1);

namespace App\Services\Contribution;

use App\Enum\ContributionActionType;
use App\Enum\EventRejectionReason;
use App\Models\ContributionHistory;
use App\Models\User;

class ContributionXPService
{
    public const XP_EVENT_APPROVED = 5;

    public static function grantXP(
        User $user,
        int $xpChange,
        ContributionActionType $action,
        string $entityType,
        int $entityId,
        ?string $note = null,
    ): void {
        if ($xpChange === 0) {
            return;
        }

        $levelBefore = $user->contribution_level;
        $newXP = $user->contribution_xp + $xpChange;
        $levelAfter = ContributionLevelService::getLevelForXP($newXP);

        $user->update([
            'contribution_xp' => $newXP,
            'contribution_level' => $levelAfter,
        ]);

        ContributionHistory::create([
            'user_id' => $user->id,
            'action_type' => $action,
            'entity_type' => $entityType,
            'entity_id' => $entityId,
            'xp_change' => $xpChange,
            'level_before' => $levelBefore,
            'level_after' => $levelAfter,
            'note' => $note,
        ]);
    }

    public static function getXPForEventApproval(): int
    {
        return self::XP_EVENT_APPROVED;
    }

    public static function getXPForEventRejection(EventRejectionReason $reason): int
    {
        return $reason->getXPChange();
    }
}
