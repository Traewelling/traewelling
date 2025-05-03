<?php

declare(strict_types=1);

namespace App\Services\PersonalDataSelection\Exporters\Base;

use Illuminate\Support\Facades\DB;

trait DatabaseExportable
{
    protected function exportData(): array {

        $condition = $this->whereCondition ?? 'id';

        $db = DB::table($this->tableName)
                ->select($this->columns)
                ->where($this->whereColumn, $this->user->{$condition});

        if (!empty($this->whereConditions)) {
            $db->where($this->whereConditions);
        }

        return $db->get()->toArray();
    }

    protected function onExportValidation(): bool {
        return !empty($this->columns) && !empty($this->tableName) && !empty($this->whereColumn);
    }
}
