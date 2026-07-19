<?php

declare(strict_types=1);

namespace App\Http\Controllers\API\v1;

use App\Http\Resources\MotisSourceLicenseResource;
use App\Models\MotisSourceLicense;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use OpenApi\Attributes as OA;

class MotisSourceController extends Controller
{
    #[OA\Get(
        path: '/motis-sources',
        operationId: 'getMotisSources',
        description: 'Returns the transit data sources used by this instance, with their license information.',
        summary: 'List transit data sources',
        tags: ['Debug'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'List of transit data sources',
                content: new OA\JsonContent(
                    required: ['data'],
                    properties: [
                        new OA\Property(property: 'data', type: 'array', items: new OA\Items(ref: MotisSourceLicenseResource::class)),
                    ],
                ),
            ),
        ],
    )]
    public function index(): AnonymousResourceCollection
    {
        $sources = MotisSourceLicense::with('manualLicense')
            ->orderBy('country')
            ->get();

        return MotisSourceLicenseResource::collection($sources);
    }
}
