<?php

declare(strict_types=1);

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\FeatureTestCase;

class EventOverviewTest extends FeatureTestCase
{
    use RefreshDatabase;

    public function test_overview_page_is_accessible(): void
    {
        $this->get(route('events'))->assertOk();
    }
}
