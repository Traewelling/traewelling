<?php

declare(strict_types=1);

namespace App\Services\Contribution;

class ContributionLevelService
{
    /**
     * Level thresholds mapping level number to minimum XP required
     */
    private const LEVEL_THRESHOLDS = [
        0 => 0,      // Level 0: 0-49 XP
        1 => 50,     // Level 1: 50-149 XP
        2 => 150,    // Level 2: 150-299 XP
        3 => 300,    // Level 3: 300-499 XP
        4 => 500,    // Level 4: 500-799 XP
        5 => 800,    // Level 5: 800-1199 XP
        6 => 1200,   // Level 6+: 1200+ XP
    ];

    /**
     * Calculate level from XP
     * Note: Negative XP is still Level 0
     */
    public static function getLevelForXP(int $xp): int
    {
        // Negative XP stays at level 0
        if ($xp < 0) {
            return 0;
        }

        $level = 0;
        foreach (self::LEVEL_THRESHOLDS as $requiredLevel => $requiredXP) {
            if ($xp >= $requiredXP) {
                $level = $requiredLevel;
            } else {
                break;
            }
        }

        return $level;
    }

    /**
     * Get minimum XP required for a specific level
     */
    public static function getXPForLevel(int $level): int
    {
        return self::LEVEL_THRESHOLDS[$level] ?? self::LEVEL_THRESHOLDS[6];
    }

    /**
     * Get XP needed for the next level
     */
    public static function getNextLevelXP(int $currentXP): int
    {
        foreach (self::LEVEL_THRESHOLDS as $level => $requiredXP) {
            if ($currentXP < $requiredXP) {
                return $requiredXP;
            }
        }

        // If at max level, return the max threshold
        return self::LEVEL_THRESHOLDS[6];
    }

    /**
     * Calculate progress percentage to next level
     */
    public static function getProgressToNextLevel(int $currentXP, int $currentLevel): float
    {
        $currentLevelXP = self::getXPForLevel($currentLevel);
        $nextLevelXP = self::getNextLevelXP($currentXP);

        // If at max level
        if ($nextLevelXP === self::LEVEL_THRESHOLDS[6] && $currentXP >= $nextLevelXP) {
            return 100.0;
        }

        $xpIntoCurrentLevel = $currentXP - $currentLevelXP;
        $xpNeededForNextLevel = $nextLevelXP - $currentLevelXP;

        if ($xpNeededForNextLevel <= 0) {
            return 100.0;
        }

        return ($xpIntoCurrentLevel / $xpNeededForNextLevel) * 100;
    }
}
