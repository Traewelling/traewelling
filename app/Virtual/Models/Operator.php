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
     *     title="id",
     *     example="sbb"
     * )
     * @var string
     */
    private $id;


    /**
     * @OA\Property (
     *     title="name",
     *     example="SBB"
     * )
     * @var string
     */
    private $name;
}
