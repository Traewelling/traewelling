<?php

declare(strict_types=1);

namespace App\Services\PersonalDataSelection\Exporters\Base;

use App\Models\User;
use InvalidArgumentException;

abstract class AbstractExporter
{
    protected User   $user;
    protected string $fileName;

    public function __construct(User $user) {
        $this->user = $user;

        if (!isset($this->fileName)) {
            throw new InvalidArgumentException('Property $fileName must be set in ' . static::class);
        }
    }

    public function getFileName(): string {
        return $this->fileName;
    }

    public function getData(): array|string {
        if (!$this->onExportValidation()) {
            throw new InvalidArgumentException('Export validation failed in ' . static::class);
        }

        if (empty($this->fileName)) {
            throw new InvalidArgumentException('Property $fileName must be set in ' . static::class);
        }

        return $this->exportData();
    }

    abstract protected function exportData(): array|string;

    abstract protected function onExportValidation(): bool;
}
