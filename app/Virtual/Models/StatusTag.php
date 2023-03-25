<?php

namespace App\Virtual\Models;

/**
 * @OA\Schema(
 *     title="StatusTag",
 *     description="StatusTag model",
 *     @OA\Xml(
 *         name="StatusTag"
 *     )
 * )
 */
class StatusTag
{

    /**
     * @OA\Property (
     *     title="key",
     *     description="Key of tag",
     *     example="trwl:ticket"
     * )
     *
     * @var string
     */
    private $key;

    /**
     * @OA\Property (
     *     title="value",
     *     description="Value of tag",
     *     example="BahnCard 100",
     * )
     *
     * @var string
     */
    private $value;

    /**
     * @OA\Property (
     *     title="visibility",
     *     description="Visibility of tag",
     *     format="int64",
     *     example="PUBLIC"
     * )
     *
     * @var string
     */
    private $visibility;
}
