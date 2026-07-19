<?php

declare(strict_types=1);

namespace Tests\Feature\APIv1;

use App\Models\MotisSourceLicense;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\ApiTestCase;

class MotisSourceTest extends ApiTestCase
{
    use RefreshDatabase;

    public function test_motis_sources_endpoint_is_public_and_returns_sources(): void
    {
        MotisSourceLicense::create([
            'country' => 'de',
            'name' => 'de-DELFI',
            'human_name' => 'DELFI e.V.',
            'source_url' => 'https://example.org/source',
            'spdx' => 'CC-BY-4.0',
            'license_url' => 'https://example.org/license',
            'attribution_text' => 'DELFI attribution',
            'active' => true,
            'force_active' => false,
        ]);

        $res = $this->getJson('/api/v1/motis-sources');

        $res->assertOk();
        $res->assertJsonCount(1, 'data');
        $res->assertJsonStructure([
            'data' => [
                '*' => [
                    'name', 'humanName', 'country', 'sourceUrl', 'spdx',
                    'licenseUrl', 'attributionText', 'active', 'forceActive', 'manualLicense',
                ],
            ],
        ]);
        $res->assertJsonPath('data.0.humanName', 'DELFI e.V.');
        $res->assertJsonPath('data.0.active', true);
    }
}
