<?php

declare(strict_types=1);

namespace App\Http\Controllers\API\v1;

use App\Http\Resources\Contribution\CommunityProfileResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * @OA\Tag(
 *     name="Community",
 *     description="Community contribution system endpoints"
 * )
 */
class CommunityController extends Controller
{
    /**
     * Get the authenticated user's contribution profile
     *
     * @OA\Get(
     *     path="/community/profile",
     *     operationId="getCommunityProfile",
     *     tags={"Community"},
     *     summary="Get your contribution profile",
     *     description="Returns contribution XP, level, and progress information for the authenticated user",
     *     security={{"passport": {}}},
     *
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="data", ref="#/components/schemas/CommunityProfile")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated"
     *     )
     * )
     */
    public function getMyProfile(Request $request): JsonResponse
    {
        $user = $request->user();

        return $this->sendResponse(new CommunityProfileResource($user));
    }
}
