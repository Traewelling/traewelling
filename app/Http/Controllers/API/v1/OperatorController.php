<?php

namespace App\Http\Controllers\API\v1;

use App\Http\Resources\OperatorResource;
use App\Models\HafasOperator;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class OperatorController extends Controller
{
    /**
     * @OA\Get(
     *      path="/operator",
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
     *      )
     * )
     *
     * @return AnonymousResourceCollection
     */
    public function index(): AnonymousResourceCollection {
        return OperatorResource::collection(HafasOperator::orderBy('name')->cursorPaginate(100));
    }
}
