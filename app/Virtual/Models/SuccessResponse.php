<?php

declare(strict_types=1);

namespace App\Virtual\Models;

use OpenApi\Attributes as OA;

#[OA\Schema(
    title: 'SuccessResponse',
    description: 'Success Response',
    xml: new OA\Xml(name: 'SuccessResponse'),
)]
class SuccessResponse
{
    #[OA\Property(
        title: 'status',
        description: 'status',
        type: 'string',
        example: 'success',
    )]
    private string $status;
}
