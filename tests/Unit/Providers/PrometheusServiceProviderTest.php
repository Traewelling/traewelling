<?php

namespace Tests\Unit\Providers;

use App\Providers\PrometheusServiceProvider;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

use function PHPUnit\Framework\assertEqualsCanonicalizing;

use Tests\ApiTestCase;

class PrometheusServiceProviderTest extends ApiTestCase
{
    use RefreshDatabase;

    private const TABLENAME = 'jobs';

    private function insertJob(string $queue, string $displayName): void
    {
        DB::table(self::TABLENAME)->insert([
            'queue' => $queue,
            'payload' => json_encode(['displayName' => $displayName]),
            'attempts' => 0,
            'available_at' => now()->timestamp,
            'created_at' => now()->timestamp,
        ]);
    }

    public function test_get_jobs_by_display_name(): void
    {
        // GIVEN: insert real rows so SQL JSON extraction can be tested end-to-end
        foreach (range(1, 4) as $_) {
            $this->insertJob('default', 'JobA');
        }
        foreach (range(1, 7) as $_) {
            $this->insertJob('webhook', 'JobB');
        }
        foreach (range(1, 2) as $_) {
            $this->insertJob('default', 'JobC');
        }
        foreach (range(1, 5) as $_) {
            $this->insertJob('webhook', 'JobC');
        }

        // WHEN
        $actual = PrometheusServiceProvider::getJobsByDisplayName(self::TABLENAME);

        // THEN: order is not guaranteed by GROUP BY, so use canonical comparison
        assertEqualsCanonicalizing([
            [4, ['JobA', 'default']],
            [7, ['JobB', 'webhook']],
            [2, ['JobC', 'default']],
            [5, ['JobC', 'webhook']],
        ], $actual);
    }
}
