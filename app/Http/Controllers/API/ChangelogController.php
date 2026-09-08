<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Resources\ChangelogResource;
use App\Services\ChangelogService;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use OpenApi\Attributes as OA;

#[OA\Get(
    path: '/v1/app/changelog',
    operationId: 'getChangelog',
    responses: [
        new OA\Response(
            response: 200,
            description: 'Successful operation',
            content: new OA\JsonContent(
                required: ['data'],
                properties: [
                    new OA\Property(
                        property: 'data',
                        type: 'array',
                        items: new OA\Items(ref: ChangelogResource::class)
                    ),
                ]
            )
        ),
    ]
)]
class ChangelogController extends Controller
{
    private ChangelogService $backendController;

    public function __construct(ChangelogService $backendController)
    {
        $this->backendController = $backendController;
    }

    public function getChangelog(): AnonymousResourceCollection
    {
        return ChangelogResource::collection($this->backendController->getChangelog());
    }
}
