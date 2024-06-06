<?php

namespace App\Virtual\Models;

use Carbon\Carbon;

/**
 * @OA\Schema(
 *     title="CheckinRequestBody",
 *     description="Fields for creating a train checkin",
 *     @OA\Xml(
 *         name="CheckinRequestBody"
 *     )
 * )
 */
class CheckinRequestBody
{
    /**
     * @OA\Property(
     *     title="body",
     *     description="Text that should be added to the post",
     *     type="string",
     *     maxLength=280,
     *     nullable=true,
     *     example="Meine erste Fahrt nach Knuffingen!"
     * )
     *
     * @var string
     */
    private $body;

    /**
     * @OA\Property (
     *     ref="#/components/schemas/Business",
     * )
     *
     * @var integer
     */
    private $business;

    /**
     * @OA\Property (
     *      ref="#/components/schemas/StatusVisibility"
     * )
     *
     * @var integer
     */
    private $visibility;

    /**
     * @OA\Property (
     *     title="eventId",
     *     nullable=true,
     *     description="Id of an event the status should be connected to",
     *     type="integer"
     * )
     */
    private $eventId;

    /**
     * @OA\Property (
     *     title="toot",
     *     nullable=true,
     *     description="Should this status be posted to mastodon?",
     *     type="boolean",
     *     example="false"
     * )
     */
    private $toot;

    /**
     * @OA\Property (
     *     title="chainPost",
     *     nullable=true,
     *     description="Should this status be posted to mastodon as a chained post?",
     *     type="boolean",
     *     example="false"
     * )
     */
    private $chainPost;

    /**
     * @OA\Property (
     *     title="ibnr",
     *     nullable=true,
     *     description="If true, the `start` and `destination` properties can be supplied as an ibnr. Otherwise they
     *     should be given as the Träwelling-ID. Default behavior is `false`.", type="boolean", example="true",
     * )
     */
    private $ibnr;

    /**
     * @OA\Property (
     *     title="tripId",
     *     description="The HAFAS tripId for the to be checked in train",
     *     example="1|323306|1|80|17072022"
     * )
     */
    private $tripId;

    /**
     * @OA\Property (
     *     title="lineName",
     *     description="The line name for the to be checked in train",
     *     example="S 4"
     * )
     */
    private $lineName;

    /**
     * @OA\Property (
     *     title="start",
     *     description="The Station-ID of the starting point (see `ibnr`)",
     *     example="8000191",
     *     type="integer"
     * )
     */
    private $start;

    /**
     * @OA\Property (
     *     title="destination",
     *     description="The Station-ID of the destination (see `ibnr`)",
     *     example="8079045",
     *     type="integer"
     * )
     */
    private $destination;

    /**
     * @OA\Property (
     *     title="departure",
     *     description="Timestamp of the departure",
     *     example="2022-12-19T20:41:00+01:00",
     * )
     *
     * @var Carbon
     */
    private $departure;

    /**
     * @OA\Property (
     *     title="arrival",
     *     description="Timestamp of the arrival",
     *     example="2022-12-19T20:42:00+01:00",
     * )
     *
     * @var Carbon
     */
    private $arrival;

    /**
     * @OA\Property (
     *     title="force",
     *     nullable=true,
     *     description="If true, the checkin will be created, even if a colliding checkin exists. No points will be
     *     awarded.", type="boolean", example="false",
     * )
     */
    private $force;

}
