<?php

namespace Tests\Feature\APIv1;

use App\Console\Commands\SchedulerCanary;
use App\Enum\CacheKey;
use Illuminate\Cache\CacheManager;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue as Queue;
use Mockery;
use Tests\TestCase;

class HealthMonitorTest extends TestCase
{
    use RefreshDatabase;

    public function setUp(): void {
        parent::setUp();

        Http::preventStrayRequests();
        Http::fake([
                       '/stations*' => Http::response([self::FRANKFURT_HBF]),
                   ]);

        $this->app->bind('cache', fn($app) => new CacheManager($app));
        $this->app->bind('cache.store', fn($app) => $app['cache']->store('array'));

        (new SchedulerCanary())->handle();
    }

    public function tearDown(): void {
        Mockery::close();
        parent::tearDown();
    }

    public function testBothRoutesReturn200OnTestingSetup(): void {
        $this->get(route("api.health.ready"))
             ->assertStatus(200)
             ->assertJson([
                              "database_reachable" => true
                          ]);

        $this->get(route("api.health.fitness"))
             ->assertStatus(200)
             ->assertJson([
                              "database_reachable" => true,
                              "queue_running"      => true,
                              "db_rest_reachable"  => true,
                              "scheduler_running"  => true,
                          ]);
    }

    public function testBothRoutesReturn500IfDatabaseIsntReachable(): void {
        DB::shouldReceive("connection")
          ->once()
          ->andThrow(new QueryException("default",
                                        "TEST QUERY",
                                        [],
                                        new \Exception("mock exception")));

        $this->get(route("api.health.ready"))
             ->assertStatus(500)
             ->assertJson(["database_reachable" => false]);
    }

    public function testFitnessRouteReturns500IfQueueIsClogged(): void {
        Queue::fake();
        Queue::shouldReceive('size')->andReturn(11);

        $this->get(route("api.health.fitness"))
             ->assertStatus(500)
             ->assertJson(["queue_running" => false]);
    }

    public function testFitnessRouteReturns500IfDbRestIsntReachable(): void {
        self::clearExistingHttpFakes();
        Http::fake([
                       '/stations*' => Http::response([self::FRANKFURT_HBF], 500),
                   ]);

        $this->get(route("api.health.fitness"))
             ->assertStatus(500)
             ->assertJson(["db_rest_reachable" => false]);
    }

    public function testFitnessRouteReturns500IfSchedulerHasNotRunInALongTime(): void {
        Cache::store("array")
             ->put(CacheKey::SchedulerCanary, time() - 600);

        $this->get(route("api.health.fitness"))
             ->assertStatus(500)
             ->assertJson(["scheduler_running" => false]);
    }

    public function testFitnessRouteReturns500IfSchedulerHasNeverRun(): void {
        Cache::store("array")
             ->forget(CacheKey::SchedulerCanary);

        $this->get(route("api.health.fitness"))
             ->assertStatus(500)
             ->assertJson(["scheduler_running" => false]);
    }

    private static function clearExistingHttpFakes(): void {
        $reflection = new \ReflectionObject(Http::getFacadeRoot());
        $property   = $reflection->getProperty('stubCallbacks');
        $property->setValue(Http::getFacadeRoot(), collect());
    }
}
