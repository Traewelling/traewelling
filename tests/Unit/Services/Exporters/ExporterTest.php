<?php

namespace Tests\Unit\Services\Exporters;

use App\Models\User;
use App\Services\PersonalDataSelection\Exporters\Base\AbstractExporter;
use App\Services\PersonalDataSelection\Exporters\Base\Exporter;
use Spatie\PersonalDataExport\PersonalDataSelection;
use Tests\Unit\UnitTestCase;

class ExporterTest extends UnitTestCase
{
    private User $user;

    private PersonalDataSelection $personalDataSelection;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->make();
        $this->personalDataSelection = $this->getMockBuilder(PersonalDataSelection::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['add'])
            ->getMock();
    }

    public function test_correct_classes()
    {
        $exporter = new Exporter($this->personalDataSelection, $this->user);
        $this->personalDataSelection->expects($this->once())->method('add')->willReturn($this->personalDataSelection);

        $exporter->export([CorrectExporter::class]);
    }

    public function test_incorrect_classes()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Tests\Unit\Services\Exporters\IncorrectExporter is not of type App\Services\PersonalDataSelection\Exporters\Base\AbstractExporter');

        $this->personalDataSelection->expects($this->never())->method('add');

        $exporter = new Exporter($this->personalDataSelection, $this->user);
        $exporter->export([IncorrectExporter::class]);
    }

    public function test_not_class()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('foobar is not of type App\Services\PersonalDataSelection\Exporters\Base\AbstractExporter');

        $this->personalDataSelection->expects($this->never())->method('add');

        $exporter = new Exporter($this->personalDataSelection, $this->user);
        $exporter->export(['foobar']);
    }

    public function test_multiple_classes()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Tests\Unit\Services\Exporters\IncorrectExporter is not of type App\Services\PersonalDataSelection\Exporters\Base\AbstractExporter');

        $this->personalDataSelection->expects($this->never())->method('add')->willReturn($this->personalDataSelection);

        $exporter = new Exporter($this->personalDataSelection, $this->user);
        $exporter->export([CorrectExporter::class, IncorrectExporter::class]);
    }
}

class IncorrectExporter
{
    // this class is not extending AbstractExporter
}

class CorrectExporter extends AbstractExporter
{
    protected string $fileName = 'test.csv';

    protected function exportData(): array|string
    {
        return 'success';
    }

    protected function onExportValidation(): bool
    {
        return true;
    }
}
