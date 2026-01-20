<?php

namespace Tests\Unit\Services\Exporters;

use App\Models\User;
use Tests\Unit\Services\Exporters\TestBaseExporter as TestExporter;
use Tests\Unit\UnitTestCase;

class BaseExporterTest extends UnitTestCase
{
    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->make();
    }

    public function test_all_params_given()
    {
        $exporter = new TestExporter($this->user, 'test.csv');

        $result = $exporter->getData();
        $this->assertEquals('success', $result);
    }

    public function test_missing_file_name()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Property $fileName must be set in Tests\Unit\Services\Exporters\TestBaseExporter');

        new TestExporter($this->user);
    }

    public function test_validation_failed()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Export validation failed in Tests\Unit\Services\Exporters\TestBaseExporter');

        $exporter = new TestExporter($this->user, 'test.csv');
        $exporter->failValidation = true;
        $exporter->getData();
    }

    public function test_empty_file_name()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Property $fileName must be set in Tests\Unit\Services\Exporters\TestBaseExporter');

        $exporter = new TestExporter($this->user, '0');
        $exporter->getData();
    }

    public function test_get_file_name()
    {
        $exporter = new TestExporter($this->user, 'test.csv');
        $this->assertEquals('test.csv', $exporter->getFileName());
    }
}
