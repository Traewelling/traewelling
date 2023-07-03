<?php

namespace App\Http\Controllers\API\v1;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Annotations as OA;

class ContributionController extends Controller
{
    /**
     * @OA\Get(
     *     path="/contribution/events/getSuggestion",
     *     operationId="contributionGetEventSuggestion",
     *     tags={"Contribution"},
     *     summary="Get the next event suggestion to be approved",
     *     description="Returns an event suggestion to be moderated and approved",
     *     @OA\Response(
     *         response=200,
     *         description="success",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="object",
     *                 ref="#/components/schemas/ContributionEventSuggestion"
     *             )
     *         ),
     *     ),
     *     @OA\Response(response=404, description="Currently no event suggestions available to fetch"),
     *     @OA\Response(response=403, description="User does not have the rights for this route"),
     *     security={
     *         {"passport": {"contribution"}}
     *     }
     * )
     */
    public function getSuggestion() {
        return "1";
    }

    /**
     * @OA\Post(
     *      path="/contribution/event/approve",
     *      operationId="submitEventModeration",
     *      tags={"Contribution"},
     *      summary="Submit a moderated event suggestion",
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(ref="#/components/schemas/ContributionEventSuggestion")
     *      ),
     *      @OA\Response(
     *          response=201,
     *          description="successful operation",
     *       ),
     *       @OA\Response(response=400, description="Bad request"),
     *       @OA\Response(response=403, description="User not authorized"),
     *       security={{"passport": {}}}
     *     )
     *
     *
     */
    public function approveSuggestion() {
        return "2";
    }

    /**
     * @OA\Post(
     *      path="/contribution/event/deny",
     *      operationId="denyEventModeration",
     *      tags={"Contribution"},
     *      summary="Deny a moderated event suggestion",
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(
     *              @OA\Property(
     *                  property="id",
     *                  type="integer",
     *                  example=1234
     *              ),
     *              @OA\Property(
     *                  property="reason",
     *                  type="string",
     *                  enum={"duplicate", "too-late", "not-applicable", "denied"},
     *                  example="not-applicable"
     *              )
     *          )
     *      ),
     *      @OA\Response(
     *          response=201,
     *          description="successful operation",
     *       ),
     *       @OA\Response(response=400, description="Bad request"),
     *       @OA\Response(response=403, description="User not authorized"),
     *       security={{"passport": {}}}
     *     )
     *
     *
     */
    public function denySuggestion() {
        return "3";
    }
}
