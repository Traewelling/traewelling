<?php

declare(strict_types=1);

namespace App\Services\PersonalDataSelection\Exporters\Base;

trait ModelExportable
{
    protected function exportData(): string {
        $condition = $this->whereCondition ?? 'id';

        return $this->model::where(
            $this->whereColumn,
            $this->user->{$condition}
        )->get()->toJson();
    }

    protected function onExportValidation(): bool {
        return !empty($this->model) && !empty($this->whereColumn);
    }
}
