<?php

namespace Tests\Unit\Services\Exporters;

use App\Models\User;
use Tests\Unit\Services\Exporters\TestBaseExporter as TestExporter;
use Tests\Unit\UnitTestCase;

class BaseExporterTest extends UnitTestCase
{
    private User $user;

    protected function setUp(): void {
        parent::setUp();
        $this->user = User::factory()->make();
    }

    public function testAllParamsGiven() {
        $exporter = new TestExporter($this->user, "test.csv");

        $result = $exporter->getData();
        $this->assertEquals("success", $result);
    }

    public function testMissingFileName() {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Property $fileName must be set in Tests\Unit\Services\Exporters\TestExporter');

        new TestExporter($this->user);
    }

    public function testValidationFailed() {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Export validation failed in Tests\Unit\Services\Exporters\TestExporter');

        $exporter                 = new TestExporter($this->user, "test.csv");
        $exporter->failValidation = true;
        $exporter->getData();
    }

    public function testEmptyFileName() {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Property $fileName must be set in Tests\Unit\Services\Exporters\TestExporter');

        $exporter = new TestExporter($this->user, "0");
        $exporter->getData();
    }

    public function testGetFileName() {
        $exporter = new TestExporter($this->user, "test.csv");
        $this->assertEquals("test.csv", $exporter->getFileName());
    }
}

