<?php

namespace Tests\Unit\Dto;

use App\Dto\Coordinate;
use App\Objects\LineSegment;
use JsonException;
use Tests\Unit\UnitTestCase;

class CoordinateTest extends UnitTestCase
{
    public function testArraySerialization(): void {
        $coordinate = new Coordinate(
            latitude:  49.013936,
            longitude: 8.404463,
        );
        $this->assertEquals(
            [8.404463, 49.013936],
            $coordinate->toArray()
        );
    }

    public function testJsonSerialization(): void {
        $coordinate = new Coordinate(
            latitude:  49.013936,
            longitude: 8.404463,
        );
        $this->assertEquals(
            [8.404463, 49.013936], //TODO: @levin - shouldn't this be a JSON string?
            $coordinate->jsonSerialize()
        );
    }

    /**
     * @dataProvider geoJsonParsingDataProvider
     * @throws JsonException
     */
    public function testGeoJsonParsing($assert, $value): void {
        $geoJson    = json_decode($value, false, 512, JSON_THROW_ON_ERROR);
        $coordinate = Coordinate::fromGeoJson($geoJson);
        $this->assertEquals($assert, $coordinate);
    }

    public static function geoJsonParsingDataProvider(): array {
        return [
            [
                new Coordinate(49.013936, 8.404463),
                '{"type": "Feature", "geometry": {"type": "Point", "coordinates": [8.404463, 49.013936]}}',
            ],
            [
                null,
                '{"type": "Feature", "geometry": {}}',
            ],
            [
                null,
                '{"type": "Feature"}',
            ],
            [
                null,
                '{"type": "Feature", "geometry": {"type": "Point"}}',
            ],
            [
                null,
                '{"type": "Feature", "geometry": {"type": "Point", "coordinates": []}}',
            ],
            [
                null,
                '{"type": "Feature", "geometry": {"type": "Point", "coordinates": [8.404463]}}',
            ],
            [
                null,
                '{"type": "Feature", "geometry": {"type": "Point", "coordinates": [8.404463, 49.013936, 1]}}',
            ],
        ];
    }

    public function testCalculateDistanceWithSameCoordinates(): void {
        $coordinate1 = new Coordinate(49.013936, 8.404463);
        $coordinate2 = new Coordinate(49.013936, 8.404463);
        $lineSegment = new LineSegment($coordinate1, $coordinate2);
        $this->assertEquals(0, $lineSegment->calculateDistance());
    }

    public function testCalculateDistanceWithDifferentCoordinates(): void {
        $coordinate1 = new Coordinate(49.013935, 8.404461);
        $coordinate2 = new Coordinate(49.013367, 8.404392);
        $lineSegment = new LineSegment($coordinate1, $coordinate2);
        $this->assertEquals(63, $lineSegment->calculateDistance());
    }

    public function testCoordinateInterpolation(): void {
        $coordinate1            = new Coordinate(49.013935, 8.404461);
        $coordinate2            = new Coordinate(49.013367, 8.404392);
        $lineSegment            = new LineSegment($coordinate1, $coordinate2);
        $interpolatedCoordinate = $lineSegment->interpolatePoint(0.5);
        $this->assertEquals(49.013651, $interpolatedCoordinate->latitude);
        $this->assertEquals(8.404427, $interpolatedCoordinate->longitude);
    }
}
