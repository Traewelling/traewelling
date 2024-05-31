<?php

namespace Tests\Unit\Casts;

use App\Casts\UTCDateTime;
use App\Models\Stopover;
use Carbon\Exceptions\InvalidTimeZoneException;
use Illuminate\Support\Carbon;
use Tests\Unit\UnitTestCase;

class UTCTimeMethodTest extends UnitTestCase
{
    public function testInvalidUtcTimestamp(): void {
        $UTCDateTime = new UTCDateTime();
        $this->expectException(InvalidTimeZoneException::class);
        $UTCDateTime->set(new Stopover(), 'departure', '2023-01-10T01:00', []);
    }

    /**
     * @dataProvider setUtcDateTimeDataProvider
     */
    public function testSetUtcDateTime($assert, $value): void {
        $UTCDateTime = new UTCDateTime();
        $result      = $UTCDateTime->set(new Stopover(), 'departure', $value, []);

        $this->assertEquals($assert, $result->toIso8601String());
    }

    public static function setUtcDateTimeDataProvider(): array {
        return [
            ['2023-01-01T00:00:00+00:00', '2023-01-01T01:00:00+01:00'],
            ['2023-01-01T00:00:00+00:00', '2023-01-01T01:00:00+01:00'],
            ['2023-01-01T00:00:00+00:00', '2023-01-01T00:00:00Z'],
            ['2023-01-01T00:00:00+00:00', '2022-12-31T16:00:00-08:00'],
            ['2023-01-01T00:00:00+00:00', new Carbon('2023-01-01T01:00:00+01:00')],
            ['2023-01-01T00:00:00+00:00', new \Carbon\Carbon('2023-01-01T01:00:00+01:00')],
        ];
    }

    /**
     * @dataProvider getUtcDateTimeDataProvider
     */
    public function testGetUtcDateTime($assert, $value): void {
        $UTCDateTime = new UTCDateTime();
        $result      = $UTCDateTime->get(new Stopover(), 'departure', $value, []);

        $this->assertEquals($assert, $result?->toIso8601String());
    }

    public static function getUtcDateTimeDataProvider(): array {
        return [
            [null, null],
            ['2023-01-01T00:00:00+00:00', '2023-01-01T00:00:00'],
            ['2023-01-01T01:00:00+01:00', new \Carbon\Carbon('2023-01-01T01:00:00+01:00')],
            ['2023-01-01T00:00:00+00:00', new \Carbon\Carbon('2023-01-01T00:00:00+00:00')],
        ];
    }
}
