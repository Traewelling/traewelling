<?php

declare(strict_types=1);

namespace App\Http\Controllers\API\v2;

use App\Http\Controllers\Controller;
use App\Http\Resources\CursorPaginationMetaResource;
use App\Http\Resources\LinksResource;
use App\Http\Resources\v2\OperatorResource;
use App\Repositories\OperatorRepository;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use OpenApi\Attributes as OA;

class OperatorController extends Controller
{
    #[OA\Get(
        path: '/v2/operators',
        operationId: 'listOperatorsV2',
        summary: 'Get a list of operators, optionally filtered by name.',
        security: [['passport' => []], ['token' => []]],
        tags: ['Checkin'],
        parameters: [
            new OA\Parameter(
                name: 'query',
                description: 'Filter operators by name (minimum 2 characters)',
                in: 'query',
                required: false,
                schema: new OA\Schema(type: 'string'),
            ),
            new OA\Parameter(name: 'cursor', in: 'query', required: false, schema: new OA\Schema(type: 'string')),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'successful operation',
                content: new OA\JsonContent(
                    required: ['data', 'links', 'meta'],
                    properties: [
                        new OA\Property(
                            property: 'data',
                            type: 'array',
                            items: new OA\Items(ref: OperatorResource::class),
                        ),
                        new OA\Property(property: 'links', ref: LinksResource::class),
                        new OA\Property(property: 'meta', ref: CursorPaginationMetaResource::class),
                    ],
                ),
            ),
            new OA\Response(response: 401, description: 'Unauthorized'),
        ],
    )]
    public function index(Request $request, OperatorRepository $operatorRepository): AnonymousResourceCollection
    {
        return OperatorResource::collection($operatorRepository->search($request->string('query')->trim()->toString()));
    }
}
