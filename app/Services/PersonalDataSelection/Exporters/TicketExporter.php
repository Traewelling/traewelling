<?php

declare(strict_types=1);

namespace App\Services\PersonalDataSelection\Exporters;

use App\Models\Ticket;
use App\Services\PersonalDataSelection\Exporters\Base\AbstractExporter;
use App\Services\PersonalDataSelection\Exporters\Base\ModelExportable;

class TicketExporter extends AbstractExporter
{
    use ModelExportable;

    protected string $fileName = 'tickets.json';

    protected string $model = Ticket::class;

    protected string $whereColumn = 'user_id';

    protected array $columns = [
        'id',
        'user_id',
        'name',
        'valid_from',
        'valid_until',
        'price',
        'currency',
        'created_at',
        'updated_at',
    ];
}
