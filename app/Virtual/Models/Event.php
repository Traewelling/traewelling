<?php

namespace App\Virtual\Models;

use Carbon\Carbon;

/**
 * @OA\Schema(
 *     title="Event",
 *     description="Event model",
 *     @OA\Xml(
 *         name="Event"
 *     )
 * )
 */
class Event
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
     *     title="name",
     *     description="Name of event",
     *     example="9-Euro-Ticket"
     * )
     *
     * @var string
     */
    private $name;

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
     * @OA\Property (
     *     title="hashtag",
     *     description="social media hashtag for event",
     *     example="NeunEuroTicket"
     * )
     *
     * @var string
     */
    private $hashtag;

    /**
     * @OA\Property (
     *     title="host",
     *     description="host of the event",
     *     example="Die Bundesregierung"
     * )
     *
     * @var string;
     */
    private $host;

    /**
     * @OA\Property (
     *     title="url",
     *     description="external URL for this event",
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
     *     title="station",
     *     description="nearest station for this event (nullable)",
     *     type="object",
     *     nullable="true",
     *     anyOf={@OA\Schema(ref="#/components/schemas/Station"), @OA\Schema(type="'null'")}
     *
     * )
     */
    private $station;
}
