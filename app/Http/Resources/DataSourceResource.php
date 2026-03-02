<?php

namespace App\Http\Resources;

use App\Models\MotisSourceLicense;
use App\Services\LicenseService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Attributes as OA;

#[OA\Schema(
    title: 'DataSourceResource',
    required: ['id', 'attribution'],
    properties: [
        new OA\Property(property: 'id', type: 'string', example: 'foobar'),
        new OA\Property(
            property: 'attribution',
            type: 'string',
            example: 'Provided by foobar under CC BY 4.0',
        ),
    ],
)]
class DataSourceResource extends JsonResource
{
    private LicenseService $licenseService;

    private ?MotisSourceLicense $source = null;

    public function __construct($resource)
    {
        if ($resource instanceof MotisSourceLicense) {
            $this->source = $resource;
        }
        parent::__construct($resource);
        $this->licenseService = app(LicenseService::class);
    }

    public function toArray(Request $request): array
    {
        $dto = null;
        if ($this->source) {
            $dto = $this->licenseService->getLicenseDataForSource($this->source);
        }

        /** @var MotisSourceLicense $this */
        return [
            'id' => $this->id,
            'attribution' => $dto?->attributionString,
        ];
    }
}
