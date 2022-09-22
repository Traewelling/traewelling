<?php

namespace App\Virtual\Models;

use Carbon\Carbon;

/**
 * @OA\Schema(
 *     title="EventDetails",
 *     description="Statistics/Details for Event",
 *     @OA\Xml(
 *         name="EventDetails"
 *     )
 * )
 */
class EventDetails
{
    /**
     * @OA\Property(
     *     title="ID",
     *     description="ID",
     *     format="int64",
     *     example=39
     * )
     *
     * @var integer
     */
    private $id;

    /**
     * @OA\Property (
     *     title="slug",
     *     description="URL-Slug for event",
     *     example="9_euro_ticket",
     * )
     *
     * @var string
     */
    private $slug;

    /**
     * @OA\Property(
     *     title="trainDistance",
     *     description="Total travelled distance for this event in meters",
     *     format="int64",
     *     example=627675656
     * )
     *
     * @var integer
     */
    private $trainDistance;

    /**
     * @OA\Property(
     *     title="trainDuration",
     *     description="Total travelled duration for this event in minutes",
     *     format="int64",
     *     example=591443
     * )
     *
     * @var integer
     */
    private $trainDuration;

}
