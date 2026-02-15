<?php

declare(strict_types=1);

namespace Tests\Unit\Enum;

use App\Enum\EventRejectionReason;
use PHPUnit\Framework\TestCase;

class EventRejectionReasonTest extends TestCase
{
    public function test_duplicate_returns_zero_xp(): void
    {
        $this->assertEquals(0, EventRejectionReason::DUPLICATE->getXPChange());
    }

    public function test_late_returns_zero_xp(): void
    {
        $this->assertEquals(0, EventRejectionReason::LATE->getXPChange());
    }

    public function test_default_returns_zero_xp(): void
    {
        $this->assertEquals(0, EventRejectionReason::DEFAULT->getXPChange());
    }

    public function test_not_applicable_returns_minus_one_xp(): void
    {
        $this->assertEquals(-1, EventRejectionReason::NOT_APPLICABLE->getXPChange());
    }

    public function test_missing_information_returns_minus_five_xp(): void
    {
        $this->assertEquals(-5, EventRejectionReason::MISSING_INFORMATION->getXPChange());
    }
}
