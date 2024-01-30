<?php

namespace Tests\Unit\Providers;

use App\Providers\PrometheusServiceProvider;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Tests\ApiTestCase;
use function PHPUnit\Framework\assertEquals;

class PrometheusServiceProviderTest extends ApiTestCase
{
    use RefreshDatabase;

    const TABLENAME = "jobs";

    public function testGetJobsByDisplayName() {
        // GIVEN
        DB::shouldReceive('table')
          ->with(self::TABLENAME)
          ->once()
          ->andReturnSelf();

        DB::shouldReceive('get')
          ->with(["queue", "payload"])
          ->andReturn(
              Collection::make(
                  array_merge([
                                  ...array_fill(0, 4, (object) ["queue" => "default", "payload" => json_encode(["displayName" => "JobA"])]),
                                  ...array_fill(0, 7, (object) ["queue" => "webhook", "payload" => json_encode(["displayName" => "JobB"])]),
                                  ...array_fill(0, 2, (object) ["queue" => "default", "payload" => json_encode(["displayName" => "JobC"])]),
                                  ...array_fill(0, 5, (object) ["queue" => "webhook", "payload" => json_encode(["displayName" => "JobC"])]),
                              ])));

        $actual = PrometheusServiceProvider::getJobsByDisplayName(self::TABLENAME);

        assertEquals([
                         [4, ["JobA", "default"]],
                         [7, ["JobB", "webhook"]],
                         [2, ["JobC", "default"]],
                         [5, ["JobC", "webhook"]],
                     ], $actual);
    }
}
