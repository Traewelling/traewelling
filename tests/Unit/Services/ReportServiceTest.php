<?php

namespace Tests\Unit\Services;

use App\Enum\Report\ReportableSubject;
use App\Enum\Report\ReportReason;
use App\Repositories\ReportRepository;
use App\Services\ReportService;
use Tests\Unit\UnitTestCase;

class ReportServiceTest extends UnitTestCase
{

    /**
     * @dataProvider checkStringProvider
     */
    public function testCheckString(array $expected, string $haystack): void {
        $reportService = new ReportService();
        $result        = $reportService->checkString($haystack);
        // Sort result
        sort($result);
        sort($expected);

        $this->assertEquals($expected, $result);
    }


    /**
     * @dataProvider checkStringProvider
     */
    public function testCheckAndReport(array $expected, string $haystack): void {
        $repository = $this->mock(ReportRepository::class);
        if ($expected === []) {
            $repository->shouldReceive('createReport')->never();
        } else {
            $info = 'Automatically reported: The Trip is inappropriate because it contains the words "' . implode('", "', $expected) . '".';

            $repository
                ->shouldReceive('createReport')
                ->once()
                ->with(
                    ReportableSubject::TRIP,
                    1,
                    ReportReason::INAPPROPRIATE,
                    $info
                );
        }

        $reportService = new ReportService(null, $repository);
        $reportService->checkAndReport($haystack, ReportableSubject::TRIP, 1);
    }

    public static function checkStringProvider(): array {
        return [
            'match first word'                                   => [
                ['auto'],
                'auto'
            ],
            'match second word'                                  => [
                ['fuss'],
                'Was fuss'
            ],
            'match multiple words'                               => [
                ['auto', 'fuss'],
                'auto und fuss'
            ],
            'match none'                                         => [
                [],
                'no match'
            ],
            'match case insensitive'                             => [
                ['auto'],
                'Auto'
            ],
            'match with special characters'                      => [
                ['fuß'],
                'fuß'
            ],
            'match with special characters and case insensitive' => [
                ['fuß'],
                'Fuß'
            ],
            'match without spaces'                               => [
                ['auto'],
                'autobus replacement'
            ],
            'match none with empty string'                       => [
                [],
                ''
            ],
            'match none with actual example'                     => [
                [],
                'ICE 123'
            ],
            'match none with u-bahn'                             => [
                [],
                'U 6'
            ],
        ];
    }

}
