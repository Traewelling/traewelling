<?php

namespace App\Virtual\Models;

/**
 * @OA\Schema(
 *     title="Stopovers",
 *     description="Stopovers of a single status",
 *     @OA\Xml(
 *         name="Stopovers"
 *     )
 * )
 */
class Stopovers
{

    /**
     * @OA\Property(
     *     property="1",
     *     description="Array of stopovers. Key describes trip id",
     *     type="array",
     *          @OA\Items(
     *              ref="#/components/schemas/TrainStopover"
     *          )
     * )
     * @var object
     */
    private $stopovers;
}
