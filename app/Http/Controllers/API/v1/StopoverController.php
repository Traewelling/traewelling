<?php

declare(strict_types=1);

namespace App\Http\Controllers\API\v1;

use App\Models\Checkin;
use App\Models\Stopover;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use OpenApi\Attributes as OA;

class StopoverController extends Controller
{
    #[OA\Delete(
        path: '/stopovers/{id}',
        operationId: 'deleteStopover',
        description: 'Admin only. Deletes a stopover, e.g. a duplicate created by a real-time refresh. Stopovers referenced by checkins cannot be deleted.',
        summary: 'Delete a stopover',
        security: [['passport' => []], ['token' => []]],
        tags: ['Trips'],
        parameters: [
            new OA\Parameter(name: 'id', description: 'Stopover ID', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 204, description: 'Stopover deleted'),
            new OA\Response(response: 403, description: 'Forbidden'),
            new OA\Response(response: 404, description: 'Not found'),
            new OA\Response(response: 409, description: 'Stopover is referenced by checkins'),
        ],
    )]
    public function destroy(int $id): Response|JsonResponse
    {
        $stopover = Stopover::findOrFail($id);
        $this->authorize('delete', $stopover);

        $isReferencedByCheckins = Checkin::where('origin_stopover_id', $stopover->id)
            ->orWhere('destination_stopover_id', $stopover->id)
            ->exists();
        if ($isReferencedByCheckins) {
            return $this->sendError('This stopover is referenced by checkins and cannot be deleted.', 409);
        }

        $stopover->delete();

        return response()->noContent();
    }
}
