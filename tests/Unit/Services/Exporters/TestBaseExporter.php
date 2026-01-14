<?php

namespace Tests\Unit\Services\Exporters;

use App\Models\User;
use App\Services\PersonalDataSelection\Exporters\Base\AbstractExporter;

// semi-exposing protected stuff because we need to test it
// yeah I know this is bad
class TestBaseExporter extends AbstractExporter
{
    public bool $failValidation = false;

    public function __construct(User $user, ?string $filename = null)
    {
        if ($filename !== null) {
            $this->fileName = $filename;
        }
        parent::__construct($user);
    }

    protected function exportData(): array|string
    {
        return 'success';
    }

    protected function onExportValidation(): bool
    {
        return !$this->failValidation;
    }
}
