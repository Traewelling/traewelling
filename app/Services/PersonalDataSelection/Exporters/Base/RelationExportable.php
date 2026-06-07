<?php

declare(strict_types=1);

namespace App\Services\PersonalDataSelection\Exporters\Base;

trait RelationExportable
{
    protected function exportData(): string|array
    {

        $relation = $this->user->{$this->relation}();

        if (!empty($this->whereColumn)) {
            $condition = $this->whereCondition ?? 'id';

            $relation->where(
                $this->whereColumn,
                $this->user->{$condition}
            );
        }

        if (!empty($this->with())) {
            $relation->with($this->with());
        }

        if (!empty($this->columns)) {
            return $relation->select($this->columns)->get()->toJson();
        }

        return $this->toJson($relation->get());
    }

    protected function toJson(mixed $model): string
    {
        return $model->toJson();
    }

    protected function onExportValidation(): bool
    {
        return !empty($this->relation);
    }

    protected function with(): array
    {
        if (empty($this->with)) {
            return [];
        }
        if (is_array($this->with)) {
            return $this->with;
        }

        return [$this->with];
    }
}
