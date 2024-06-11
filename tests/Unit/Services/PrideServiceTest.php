<?php

namespace Tests\Unit\Services;

use App\Services\PrideService;
use Tests\Unit\UnitTestCase;

class PrideServiceTest extends UnitTestCase
{

    public function testPrideMonthDetection(): void {
        $this->travelTo('2024-01-01 12:00:00');
        $this->assertFalse(PrideService::isPrideMonth());

        $this->travelTo('2024-06-01 12:00:00');
        $this->assertTrue(PrideService::isPrideMonth());
    }

    public function testCssClasses(): void {
        $this->travelTo('2024-01-01 12:00:00');
        $this->assertNull(PrideService::getCssClassesForPrideFlag());

        $this->travelTo('2024-06-01 12:00:00');
        $this->assertStringContainsString('text-pride', PrideService::getCssClassesForPrideFlag());
    }
}
