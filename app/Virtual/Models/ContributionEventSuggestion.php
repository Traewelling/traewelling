<?php

namespace App\Virtual\Models;

use Carbon\Carbon;
use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 *     title="ContributionEventSuggestion",
 *     description="All required properties to make an event out of an event suggestion",
 *     @OA\Xml(
 *         name="ContributionEventSuggestion"
 *     )
 * )
 */
class ContributionEventSuggestion
{
    /**
     * @OA\Property(
     *     title="id",
     *     type="integer",
     *     example=1234
     * )
     *
     * @var integer
     */
    private $id;

    /**
     * @OA\Property(
     *     title="name",
     *     description="name of the event",
     *     type="string",
     *     maxLength=255,
     *     example="Eröffnung der Nebenbahn in Knuffingen"
     * )
     *
     * @var string
     */
    private $name;

    /**
     * @OA\Property(
     *     title="hashtag",
     *     description="hashtag of the event",
     *     type="string",
     *     maxLength=25,
     *     example="NebenbahnKnuffingen"
     * )
     *
     * @var string
     */
    private $hashtag;

    /**
     * @OA\Property (
     *     title="host",
     *     description="host of the event",
     *     nullable=true,
     *     example="MiWuLa"
     * )
     *
     * @var string
     */
    private $host;

    /**
     * @OA\Property (
     *     title="nearestStation",
     *     description="station nearest to the event",
     *     nullable=true,
     *     example="Hamburg Hbf"
     * )
     *
     * @var string
     */
    private $nearestStation;

    /**
     * @OA\Property (
     *     title="url",
     *     nullable=true,
     *     description="external URL for this event",
     *     type="string",
     *     maxLength=255,
     *     example="https://www.bundesregierung.de/breg-de/aktuelles/faq-9-euro-ticket-2028756"
     * )
     *
     * @var string
     */
    private $url;

    /**
     * @OA\Property (
     *     title="begin",
     *     description="Timestamp for the start of the event",
     *     example="2022-06-01T00:00:00+02:00"
     * )
     *
     * @var Carbon
     */
    private $begin;

    /**
     * @OA\Property (
     *     title="end",
     *     description="Timestamp for the end of the event",
     *     example="2022-08-31T23:59:00+02:00"
     * )
     *
     * @var Carbon
     */
    private $end;

    /**
     * @OA\Property (
     *     title="checkinBegin",
     *     description="Timestamp for the start of the event showing up in the checkin modal",
     *     example="2022-06-01T00:00:00+02:00",
     *     nullable=true
     * )
     *
     * @var Carbon
     */
    private $checkinBegin;

    /**
     * @OA\Property (
     *     title="checkinEnd",
     *     description="Timestamp for the end of the event showing up in the checkin modal",
     *     example="2022-08-31T23:59:00+02:00",
     *     nullable=true
     * )
     *
     * @var Carbon
     */
    private $checkinEnd;

}
