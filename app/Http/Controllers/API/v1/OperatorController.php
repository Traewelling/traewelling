<?php

namespace App\Http\Controllers\API\v1;

use App\Http\Resources\OperatorResource;
use App\Models\Operator;
use App\Services\OperatorService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use OpenApi\Attributes as OA;

class OperatorController extends Controller
{
    #[OA\Get(
        path: '/operators',
        operationId: 'getOperators',
        summary: 'Get a list of operators, optionally filtered by name.',
        tags: ['Checkin'],
        parameters: [
            new OA\Parameter(
                name: 'query',
                description: 'Filter operators by name (minimum 2 characters)',
                in: 'query',
                required: false,
                schema: new OA\Schema(type: 'string'),
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: self::OA_DESC_SUCCESS,
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'data',
                            type: 'array',
                            items: new OA\Items(ref: OperatorResource::class),
                        ),
                    ],
                ),
            ),
            new OA\Response(response: 401, description: self::OA_DESC_UNAUTHENTICATED),
            new OA\Response(response: 403, description: self::OA_DESC_FORBIDDEN),
        ],
    )]
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = $request->string('query')->trim();

        $builder = Operator::with('identifiers')->orderBy('name');

        if ($query->isNotEmpty() && $query->length() >= 2) {
            $builder->where('name', 'like', '%' . $query . '%');
        }

        return OperatorResource::collection($builder->cursorPaginate(25));
    }

    #[OA\Put(
        path: '/operators/{oldOperatorId}/merge/{newOperatorId}',
        operationId: 'mergeOperators',
        summary: 'Merge two operators into one (admin only).',
        tags: ['Checkin'],
        parameters: [
            new OA\Parameter(
                name: 'oldOperatorId',
                description: 'UUID of the operator to merge from.',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'string', format: 'uuid'),
            ),
            new OA\Parameter(
                name: 'newOperatorId',
                description: 'UUID of the operator to merge into.',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'string', format: 'uuid'),
            ),
        ],
        responses: [
            new OA\Response(response: 204, description: self::OA_DESC_NO_CONTENT),
            new OA\Response(response: 401, description: self::OA_DESC_UNAUTHENTICATED),
            new OA\Response(response: 403, description: self::OA_DESC_FORBIDDEN),
            new OA\Response(response: 404, description: self::OA_DESC_NOT_FOUND),
        ],
    )]
    public function merge(string $oldOperatorId, string $newOperatorId): JsonResponse
    {
        $oldOperator = Operator::findOrFail($oldOperatorId);
        $newOperator = Operator::findOrFail($newOperatorId);

        // check if user is allowed to update and delete operators - because merging is a combination of both
        $this->authorize('update', $newOperator);
        $this->authorize('delete', $oldOperator);

        try {
            $operatorService = new OperatorService();
            $operatorService->mergeOperators($oldOperator, $newOperator);

            return response()->json(null, 204);
        } catch (Exception $exception) {
            report($exception);

            return response()->json(['error' => 'Failed to merge operators'], 500);
        }
    }
}
