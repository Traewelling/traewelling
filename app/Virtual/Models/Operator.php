<?php

namespace App\Virtual\Models;

/**
 * @OA\Schema(
 *     title="Operator",
 *     description="Operator of a mean of transport",
 *     @OA\Xml(
 *         name="Operator"
 *     )
 * )
 */
class Operator
{
    /**
     * @OA\Property (
     *     title="identifier",
     *     example="sbb"
     * )
     * @var string
     */
    private $identifier;


    /**
     * @OA\Property (
     *     title="name",
     *     example="SBB"
     * )
     * @var string
     */
    private $name;
}
