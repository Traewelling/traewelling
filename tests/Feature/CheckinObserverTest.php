<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Helpers\CacheKey;
use App\Models\Checkin;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\FeatureTestCase;

class CheckinObserverTest extends FeatureTestCase
{
    use RefreshDatabase;

    private function seedCache(int $userId, Carbon $departure): string
    {
        $key = CacheKey::getIcsUserMonthlyKey($userId, $departure);
        Cache::put($key, [['cached' => true]], now()->addHour());

        return $key;
    }

    public function test_creating_checkin_clears_ics_cache_for_departure_month(): void
    {
        $user = User::factory()->create();
        $departure = Carbon::parse('2025-06-15 08:00:00', 'UTC');

        $key = $this->seedCache($user->id, $departure);
        $this->assertTrue(Cache::has($key));

        Checkin::factory(['user_id' => $user->id])->create(['departure' => $departure]);

        $this->assertFalse(Cache::has($key));
    }

    public function test_deleting_checkin_clears_ics_cache_for_departure_month(): void
    {
        $user = User::factory()->create();
        $departure = Carbon::parse('2025-06-15 08:00:00', 'UTC');
        $checkin = Checkin::factory(['user_id' => $user->id])->create(['departure' => $departure]);

        $key = $this->seedCache($user->id, $departure);
        $this->assertTrue(Cache::has($key));

        $checkin->delete();

        $this->assertFalse(Cache::has($key));
    }

    public function test_updating_departure_clears_ics_cache_for_old_and_new_month(): void
    {
        $user = User::factory()->create();
        $oldDeparture = Carbon::parse('2025-05-20 08:00:00', 'UTC');
        $newDeparture = Carbon::parse('2025-06-15 08:00:00', 'UTC');
        $checkin = Checkin::factory(['user_id' => $user->id])->create(['departure' => $oldDeparture]);

        $oldKey = $this->seedCache($user->id, $oldDeparture);
        $newKey = $this->seedCache($user->id, $newDeparture);
        $this->assertTrue(Cache::has($oldKey));
        $this->assertTrue(Cache::has($newKey));

        $checkin->update(['departure' => $newDeparture]);

        $this->assertFalse(Cache::has($oldKey));
        $this->assertFalse(Cache::has($newKey));
    }

    public function test_updating_unrelated_field_does_not_clear_ics_cache(): void
    {
        $user = User::factory()->create();
        $departure = Carbon::parse('2025-06-15 08:00:00', 'UTC');
        $checkin = Checkin::factory(['user_id' => $user->id])->create(['departure' => $departure])->fresh();

        $key = $this->seedCache($user->id, $departure);
        $this->assertTrue(Cache::has($key));

        $checkin->update(['points' => 999]);

        $this->assertTrue(Cache::has($key));
    }
}
