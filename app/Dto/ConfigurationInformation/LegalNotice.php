<?php

declare(strict_types=1);

namespace App\Dto\ConfigurationInformation;

use App\Traits\JsonResponseObject;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'LegalNotice',
    description: 'Contact details of the operator for the legal notice.',
    required: ['name', 'address1', 'address2', 'email', 'phone'],
    properties: [
        new OA\Property(property: 'name', type: 'string', example: 'John Doe', nullable: true),
        new OA\Property(property: 'address1', type: 'string', example: '1 Example Street', nullable: true),
        new OA\Property(property: 'address2', type: 'string', example: '12345 Example City', nullable: true),
        new OA\Property(property: 'email', type: 'string', example: 'hello@example.com', nullable: true),
        new OA\Property(property: 'phone', type: 'string', example: '+49 123 456789', nullable: true),
    ],
    type: 'object'
)]
readonly class LegalNotice
{
    use JsonResponseObject;

    public function __construct(
        public ?string $name,
        public ?string $address1,
        public ?string $address2,
        public ?string $email,
        public ?string $phone,
    ) {}
}
