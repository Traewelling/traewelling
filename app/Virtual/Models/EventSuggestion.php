<?php

namespace App\Virtual\Models;

use Carbon\Carbon;

/**
 * @OA\Schema(
 *     title="EventSuggestion",
 *     description="Fields for suggesting an event",
 *     @OA\Xml(
 *         name="EventSuggestion"
 *     )
 * )
 */
class EventSuggestion
{
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
     *     title="hashtag",
     *     nullable=true,
     *     description="hashtag for this event",
     *     type="string",
     *     maxLength=40,
     *     example="gpn21"
     * )
     *
     * @var string
     */
    private $hashtag;

    /**
     * @OA\Property (
     *     title="nearestStation",
     *     nullable=true,
     *     description="Query string for the nearest station to this event",
     *     type="string",
     *     maxLength=255,
     *     example="Berlin Hbf"
     * )
     *
     * @var string
     */
    private $nearestStation;

}
