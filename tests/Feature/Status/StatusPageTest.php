<?php

declare(strict_types=1);

namespace Tests\Feature\Status;

use App\Models\Checkin;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\FeatureTestCase;

class StatusPageTest extends FeatureTestCase
{
    use RefreshDatabase;

    public function test_status_page_is_served_under_its_uuid(): void
    {
        $status = Checkin::factory()->create()->status;

        $this->get('/status/' . $status->uuid)
            ->assertOk()
            ->assertSee('<link rel="canonical" href="' . url('/status/' . $status->uuid) . '"', false);

        $this->get('/status/00000000-0000-0000-0000-000000000000')->assertNotFound();
    }

    public function test_numeric_status_url_redirects_permanently_to_uuid(): void
    {
        $status = Checkin::factory()->create()->status;

        $this->get('/status/' . $status->id)
            ->assertMovedPermanently()
            ->assertRedirect('/status/' . $status->uuid);

        $this->get('/status/' . ($status->id + 1))->assertNotFound();
    }
}
