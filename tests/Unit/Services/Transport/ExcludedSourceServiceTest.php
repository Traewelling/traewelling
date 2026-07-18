<?php

declare(strict_types=1);

namespace Tests\Unit\Services\Transport;

use App\Services\Transport\ExcludedSourceService;
use Tests\Unit\UnitTestCase;

class ExcludedSourceServiceTest extends UnitTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['trwl.motis.excluded_sources' => ['de-amarillo-bw', 'xx-bad-feed']]);
    }

    public function test_identifier_of_excluded_source_is_detected(): void
    {
        $service = new ExcludedSourceService();

        $this->assertTrue($service->isExcluded('de-amarillo-bw_de:08221:1160'));
        $this->assertTrue($service->isExcluded('xx-bad-feed_something:1'));
    }

    public function test_identifier_of_allowed_source_is_not_excluded(): void
    {
        $service = new ExcludedSourceService();

        $this->assertFalse($service->isExcluded('de-DELFI_de:09184:2600'));
        $this->assertFalse($service->isExcluded('cz-Bean-Shuttle_2'));
    }

    public function test_source_name_without_underscore_boundary_is_not_matched(): void
    {
        $service = new ExcludedSourceService();

        // must only match the "{source}_" prefix, not a source name that is merely contained
        $this->assertFalse($service->isExcluded('de-amarillo-bw-extra_de:1'));
        $this->assertFalse($service->isExcluded('other-de-amarillo-bw_de:1'));
    }

    public function test_null_and_empty_identifiers_are_not_excluded(): void
    {
        $service = new ExcludedSourceService();

        $this->assertFalse($service->isExcluded(null));
        $this->assertFalse($service->isExcluded(''));
    }

    public function test_empty_excluded_list_excludes_nothing(): void
    {
        config(['trwl.motis.excluded_sources' => []]);
        $service = new ExcludedSourceService();

        $this->assertFalse($service->isExcluded('de-amarillo-bw_de:08221:1160'));
    }
}
