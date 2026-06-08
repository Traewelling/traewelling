<?php

declare(strict_types=1);

namespace App\Services\PersonalDataSelection\Exporters\Base;

trait ModelExportable
{
    protected function exportData(): array
    {
        $condition = $this->whereCondition ?? 'id';

        return $this->model::where(
            $this->whereColumn,
            $this->user->{$condition}
        )->get()->toArray();
    }

    protected function onExportValidation(): bool
    {
        return !empty($this->model) && !empty($this->whereColumn);
    }
}
