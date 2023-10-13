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

        DB::shouldReceive("get")
          ->with("payload")
          ->andReturn(
              Collection::make(
                  array_merge([
                                  ...array_fill(0, 4, (object) ["payload" => json_encode(["displayName" => "JobA"])]),
                                  ...array_fill(0, 7, (object) ["payload" => json_encode(["displayName" => "JobB"])]),
                                  ...array_fill(0, 2, (object) ["payload" => json_encode(["displayName" => "JobC"])]),
                              ])));

        $actual = PrometheusServiceProvider::getJobsByDisplayName(self::TABLENAME);

        assertEquals([
                         [4, ["JobA"]],
                         [7, ["JobB"]],
                         [2, ["JobC"]]
                     ], $actual);
    }
}
