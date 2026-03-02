<?php

namespace App\Http\Controllers\API\v1;

use App\Http\Resources\OperatorResource;
use App\Models\Operator;
use App\Services\OperatorService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use OpenApi\Attributes as OA;

class OperatorController extends Controller
{
    #[OA\Get(
        path: '/operators',
        summary: 'Get a list of all operators.',
        tags: ['Checkin'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'successful operation',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'data',
                            type: 'array',
                            items: new OA\Items(ref: '#/components/schemas/OperatorResource'),
                        ),
                    ],
                ),
            ),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 403, description: 'Unauthorized'),
            new OA\Response(response: 500, description: 'Internal Server Error'),
        ],
    )]
    public function index(): AnonymousResourceCollection
    {
        return OperatorResource::collection(Operator::orderBy('name')->cursorPaginate(250));
    }

    public function merge(int $oldOperatorId, int $newOperatorId): JsonResponse
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
