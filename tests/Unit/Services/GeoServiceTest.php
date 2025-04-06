<?php

namespace Tests\Unit\Services;

use App\Dto\Coordinate;
use App\Services\GeoService;
use Tests\Unit\UnitTestCase;

class GeoServiceTest extends UnitTestCase
{
    private GeoService $geoService;

    public static function distanceProvider() {
        return [
            'Hanover → Karlsruhe'   => [52.376589, 9.741083, 48.993962, 8.401107, 388213],
            'Hanover → Hanover'     => [52.376589, 9.741083, 52.376589, 9.741083, 0],
            'Hanover → Kroepcke'    => [52.376589, 9.741083, 52.374497, 9.738573, 289],
            'Karlsruhe → Hanover'   => [48.993962, 8.401107, 52.376589, 9.741083, 388213],
            'Karlsruhe → Karlsruhe' => [48.993962, 8.401107, 48.993962, 8.401107, 0],
        ];
    }

    public static function boundingBoxProvider(): array {
        return [
            'Hannover'  => [52.376589, 9.741083, 1000, 52.385572, 9.755798, 52.367606, 9.726368],
            'Karlsruhe' => [48.993140, 8.402009, 101, 48.994047, 8.403392, 48.992233, 8.400626],
        ];
    }

    protected function setUp(): void {
        parent::setUp();
        $this->geoService = new GeoService();
    }

    /**
     * @dataProvider distanceProvider
     */
    public function testDistance($startLat, $startLon, $endLat, $endLon, $expected): void {
        $result = $this->geoService->getDistance(
            new Coordinate($startLat, $startLon),
            new Coordinate($endLat, $endLon)
        );
        $this->assertEquals($expected, $result);
    }

    /**
     * @dataProvider boundingBoxProvider
     */
    public function testBoundingBox($lat, $lon, $radius, $topLeftLat, $topLeftLon, $bottomRightLat, $bottomRightLon): void {
        $center      = new Coordinate($lat, $lon);
        $boundingBox = $this->geoService->getBoundingBox($center, $radius);

        $this->assertEquals($topLeftLat, $boundingBox->upperLeft->latitude);
        $this->assertEquals($topLeftLon, $boundingBox->upperLeft->longitude);
        $this->assertEquals($bottomRightLat, $boundingBox->lowerRight->latitude);
        $this->assertEquals($bottomRightLon, $boundingBox->lowerRight->longitude);
    }
}
