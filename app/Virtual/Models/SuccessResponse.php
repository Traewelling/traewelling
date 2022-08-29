<?php

namespace App\Virtual\Models;

/**
 * @OA\Schema(
 *     title="SuccessResponse",
 *     description="Success Response",
 *     @OA\Xml(
 *         name="SuccessResponse"
 *     )
 * )
 */
class SuccessResponse
{
    /**
     * @OA\Property(
     *     title="status",
     *     description="status",
     *     example="success"
     * )
     *
     * @var string
     **/
    private $status;
}
