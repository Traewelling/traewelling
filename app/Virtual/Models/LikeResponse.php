<?php

namespace App\Virtual\Models;

/**
 * @OA\Schema(
 *     title="LikeResponse",
 *     @OA\Xml(
 *         name="LikeResponse"
 *     )
 * )
 */
class LikeResponse
{
    /**
     * @OA\Property(
     *     title="count",
     *     description="Amount of likes",
     *     example=12,
     *     type="integer",
     *     format="int32"
     * )
     *
     * @var string
     **/
    private $count;
}
