<?php

namespace App\Http\Controllers\API\v1;

use App\Http\Resources\OperatorResource;
use App\Models\HafasOperator;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class OperatorController extends Controller
{
    /**
     * @OA\Get(
     *      path="/operators",
     *      summary="Get a list of all operators.",
     *      tags={"Checkin"},
     *      @OA\Response(
     *          response=200,
     *          description="successful operation",
     *          @OA\JsonContent(
     *              @OA\Property(
     *                  property="data",
     *                  type="array",
     *                  @OA\Items(ref="#/components/schemas/OperatorResource")
     *              )
     *          )
     *      ),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=403, description="Unauthorized"),
     *     @OA\Response(response=500, description="Internal Server Error")
     * )
     *
     * @return AnonymousResourceCollection
     */
    public function index(): AnonymousResourceCollection {
        return OperatorResource::collection(HafasOperator::orderBy('name')->cursorPaginate(250));
    }
}
