<?php

namespace App\Virtual\Models;

use Carbon\Carbon;

/**
 * @OA\Schema (
 *     title="TrainStation",
 *     description="train station model",
 *     @OA\Xml(
 *        name="TrainStation"
 *     )
 * )
 */
class TrainStation
{
    /**
     * @OA\Property (
     *     title="id",
     *     description="id",
     *     example="4711"
     * )
     *
     * @var integer
     */
    private $id;

    /**
     * @OA\Property(
     *     title="name",
     *     description="name of the station",
     *     example="Karlsruhe Hbf"
     * )
     *
     * @var string
     */
    private $name;

    /**
     * @OA\Property(
     *     title="latitude",
     *     description="latitude of the station",
     *     example="48.991591"
     * )
     *
     * @var float
     */
    private $latitude;

    /**
     * @OA\Property(
     *     title="longitude",
     *     description="longitude of the station",
     *     example="8.400538"
     * )
     *
     * @var float
     */
    private $longitude;

    /**
     * @OA\Property(
     *     title="ibnr",
     *     description="IBNR of the station",
     *     example="8000191"
     * )
     *
     * @var int
     */
    private $ibnr;

    /**
     * @OA\Property (
     *     title="rilIdentifier",
     *     description="Identifier specified in 'Richtline 100' of the Deutsche Bahn",
     *     nullable="true",
     *     example="RK"
     * )
     *
     * @var string
     */
    private $rilIdentifier;
}
