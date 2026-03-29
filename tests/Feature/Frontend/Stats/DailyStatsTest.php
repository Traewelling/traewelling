<?php

declare(strict_types=1);

namespace Tests\Feature\Frontend\Stats;

use App\Models\Checkin;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\FeatureTestCase;

class DailyStatsTest extends FeatureTestCase
{
    use RefreshDatabase;

    /**
     * Regression test for #4598: when a trip departs just before UTC midnight but lands on the
     * next local day, the "previous date" navigation must point to the day
     * BEFORE the current date, not to the current date itself (which would create an infinite loop).
     */
    public function test_prev_date_does_not_loop_for_midnight_crossing_trip(): void
    {
        // User is in UTC+2 (Europe/Helsinki, winter time).
        $user = User::factory()->create(['timezone' => 'Europe/Helsinki']);
        $this->actingAs($user);

        // Trip A: departs 2026-03-14 23:30 UTC = 2026-03-15 01:30 local (crosses midnight).
        // This trip belongs to local day 2026-03-15 but is just before UTC midnight.
        $tripADep = Carbon::parse('2026-03-14 23:30:00', 'UTC');
        Checkin::factory([
            'user_id' => $user->id,
            'departure' => $tripADep,
            'arrival' => $tripADep->clone()->addHours(2),
        ])->create();

        // Trip B: departs 2026-03-14 12:00 UTC = 2026-03-14 14:00 local.
        // This is the expected "previous" trip when viewing 2026-03-15.
        $tripBDep = Carbon::parse('2026-03-14 12:00:00', 'UTC');
        Checkin::factory([
            'user_id' => $user->id,
            'departure' => $tripBDep,
            'arrival' => $tripBDep->clone()->addHours(1),
        ])->create();

        $response = $this->get(route('stats.daily', ['dateString' => '2026-03-15']));
        $response->assertOk();

        /** @var Carbon|null $prevDate */
        $prevDate = $response->viewData('prevDate');

        // prevDate must be 2026-03-14, not 2026-03-15 (loop) and not null.
        $this->assertNotNull($prevDate, 'prevDate should not be null, trip B exists on 2026-03-14');
        $this->assertSame(
            '2026-03-14',
            $prevDate->format('Y-m-d'),
            'prevDate must be 2026-03-14, not the current date (2026-03-15)',
        );
    }
}
