<?php

namespace Tests\Unit\Services;

use App\Services\ReportService;
use Tests\Unit\UnitTestCase;

class ReportServiceTest extends UnitTestCase
{

    /**
     * @dataProvider testCheckStringProvider
     */
    public function testCheckString(array $expected, string $haystack): void {
        $reportService = new ReportService();
        $result = $reportService->checkString($haystack);
        // Sort result
        sort($result);
        sort($expected);

        $this->assertEquals($expected, $result);
    }

    public static function testCheckStringProvider(): array {
        return [
            'match first word' => [
                ['auto'],
                'auto'
            ],
            'match second word' => [
                ['fuss'],
                'Was fuss'
            ],
            'match multiple words' => [
                ['auto', 'fuss'],
                'auto und fuss'
            ],
            'match none' => [
                [],
                'no match'
            ],
            'match case insensitive' => [
                ['auto'],
                'Auto'
            ],
            'match with special characters' => [
                ['fuß'],
                'fuß'
            ],
            'match with special characters and case insensitive' => [
                ['fuß'],
                'Fuß'
            ],
            'match without spaces' => [
                ['auto'],
                'autobus replacement'
            ],
        ];
    }

}
