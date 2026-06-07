<?php

declare(strict_types=1);

namespace App\Enum;

use OpenApi\Attributes as OA;

#[OA\Schema(
    title: 'ExportableFileType',
    description: 'The file type to export the data in. The available columns depend on the file type.',
    type: 'string',
    enum: [
        'pdf',
        'csv_human',
        'csv_machine',
        'json',
    ],
)]
enum ExportableFileType: string
{
    case PDF = 'pdf';
    case CSV_HUMAN = 'csv_human';
    case CSV_MACHINE = 'csv_machine';
    case JSON = 'json';
}
