<?php

namespace App\Virtual\Models;

use Carbon\Carbon;

/**
 * @OA\Schema (
 *     title="Stopover",
 *     description="stopover model",
 *     @OA\Xml(
 *        name="Stopover"
 *     )
 * )
 */
class Stopover
{
    /**
     * @OA\Property (
     *     title="id",
     *     description="id",
     *     example="12089"
     * )
     *
     * @var integer
     */
    private $id;

    /**
     * @OA\Property (
     *     title="name",
     *     description="name of the station",
     *     example="Karlsruhe Hbf"
     * )
     *
     * @var string
     */
    private $name;

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

    /**
     * @OA\Property (
     *     title="evaIdentifier",
     *     description="IBNR identifier of Deutsche Bahn",
     *     example="8000191"
     * )
     *
     * @var integer
     */
    private $evaIdentifier;

    /**
     * @OA\Property (
     *     title="arrival",
     *     description="currently known arrival time. Equal to arrivalReal if known. Else equal to arrivalPlanned.",
     *     nullable=true,
     *     example="2022-07-17T14:17:00+02:00"
     * )
     *
     * @var Carbon
     */
    private $arrival;

    /**
     * @OA\Property (
     *     title="arrivalPlanned",
     *     description="planned arrival according to timetable records",
     *     nullable=true,
     *     example="2022-07-17T14:17:00+02:00"
     * )
     *
     * @var Carbon
     */
    private $arrivalPlanned;

    /**
     * @OA\Property (
     *     title="arrivalReal",
     *     description="real arrival according to live data",
     *     nullable=true,
     *     example="2022-07-17T14:17:00+02:00"
     * )
     *
     * @var Carbon
     */
    private $arrivalReal;

    /**
     * @OA\Property (
     *     title="arrivalPlatformPlanned",
     *     description="planned arrival platform according to timetable records",
     *     nullable=true,
     *     example="3"
     * )
     *
     * @var string
     */
    private $arrivalPlatformPlanned;

    /**
     * @OA\Property (
     *     title="arrivalPlatformReal",
     *     description="real arrival platform according to live data",
     *     nullable=true,
     *     example="3"
     * )
     *
     * @var string
     */
    private $arrivalPlatformReal;

    /**
     * @OA\Property (
     *     title="departure",
     *     description="currently known departure time. Equal to departureReal if known. Else equal to
     *     departurePlanned.", nullable=true, example="2022-07-17T14:17:00+02:00"
     * )
     *
     * @var Carbon
     */
    private $departure;

    /**
     * @OA\Property (
     *     title="departurePlanned",
     *     description="planned departure according to timetable records",
     *     nullable=true,
     *     example="2022-07-17T14:17:00+02:00"
     * )
     *
     * @var Carbon
     */
    private $departurePlanned;

    /**
     * @OA\Property (
     *     title="departureReal",
     *     description="real departure according to live data",
     *     nullable=true,
     *     example="2022-07-17T14:17:00+02:00"
     * )
     *
     * @var Carbon
     */
    private $departureReal;

    /**
     * @OA\Property (
     *     title="departurePlatformPlanned",
     *     description="planned departure platform according to timetable records",
     *     nullable=true,
     *     example="3"
     * )
     *
     * @var string
     */
    private $departurePlatformPlanned;

    /**
     * @OA\Property (
     *     title="departurePlatformReal",
     *     description="real departure platform according to live data",
     *     nullable=true,
     *     example="3"
     * )
     *
     * @var string
     */
    private $departurePlatformReal;

    /**
     * @OA\Property (
     *     title="platform",
     *     description="platform",
     *     nullable="true",
     *     example="3"
     * )
     *
     * @var string
     */
    private $platform;

    /**
     * @OA\Property (
     *     title="isArrivalDelayed",
     *     description="Is there a delay in the arrival time?",
     *     example=false
     * )
     *
     * @var bool
     */
    private $isArrivalDelayed;

    /**
     * @OA\Property (
     *     title="isDepartureDelayed",
     *     description="Is there a delay in the departure time?",
     *     example=false
     * )
     *
     * @var bool
     */
    private $isDepartureDelayed;

    /**
     * @OA\Property (
     *     title="cancelled",
     *     description="is this stopover cancelled?",
     *     example=false
     * )
     *
     * @var bool
     */
    private $cancelled;


}
