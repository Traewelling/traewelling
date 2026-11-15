<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\Operator;
use Illuminate\Contracts\Pagination\CursorPaginator;

class OperatorRepository
{
    /**
     * Filters by name only if the query has at least two characters.
     */
    public function search(string $query): CursorPaginator
    {
        $builder = Operator::with('identifiers')->orderBy('name');

        if (mb_strlen($query) >= 2) {
            $builder->where('name', 'like', '%' . $query . '%');
        }

        return $builder->cursorPaginate(25);
    }
}
