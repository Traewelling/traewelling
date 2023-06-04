<?php

namespace App\Virtual\Models;

/**
 * @OA\Schema(
 *     title="Notification",
 *     description="Notification model",
 *     @OA\Xml(
 *         name="Notification"
 *     )
 * )
 */
class Notification
{
    /**
     * @OA\Property(
     *     title="ID",
     *     description="ID",
     *     format="string",
     *     example="bb1ba9a5-9c2b-43c3-b8c9-2f70651fc51c"
     * )
     *
     * @var string
     **/
    private $id;

    /**
     * @OA\Property (
     *     title="type",
     *     description="type of notification",
     *     example="UserJoinedConnection"
     * )
     *
     * @var string
     */
    private $type;

    /**
     * @OA\Property(
     *     title="leadFormatted",
     *     description="the title of notification in html formatted form",
     *     format="string",
     *     example="<b>@bob</b> is in your connection!"
     * )
     *
     * @var string
     */
    private $leadFormatted;

    /**
     * @OA\Property(
     *     title="lead",
     *     description="the title of notification in plain text form",
     *     format="string",
     *     example="@bob is in your connection!"
     * )
     *
     * @var string
     */
    private $lead;

    /**
     * @OA\Property(
     *     title="noticeFormatted",
     *     description="the body of notification in html formatted form",
     *     format="string",
     *     example="@bob is on <b>S 81</b> from <b>Karlsruhe Hbf</b> to <b>Freudenstadt Hbf</b>."
     * )
     *
     * @var string
     */
    private $noticeFormatted;

    /**
     * @OA\Property(
     *     title="notice",
     *     description="the body of notification in plain text form",
     *     format="string",
     *     example="@bob is on S 81 from Karlsruhe Hbf to Freudenstadt Hbf."
     * )
     *
     * @var string
     */
    private $notice;

    /**
     * @OA\Property(
     *     title="link",
     *     description="the link to the notification",
     *     format="string",
     *     example="https://traewelling.de/status/123456"
     * )
     *
     * @var string
     */
    private $link;

    /**
     * @OA\Property(
     *     title="data",
     *     description="the data of the notification",
     *     @OA\Items(
     *          example={"notice": "every notification type has different data attributes, just try it out"}
     *     )
     * )
     *
     * @var array
     */
    private $data;

    /**
     * @OA\Property(
     *     title="readAt",
     *     description="the date when the notification was read, null if not read yet",
     *     format="string",
     *     nullable=true,
     *     example="2023-01-01T00:00:00+00:00"
     * )
     *
     * @var string
     */
    private $readAt;

    /**
     * @OA\Property(
     *     title="createdAt",
     *     description="the date when the notification was created",
     *     format="string",
     *     example="2023-01-01T00:00:00+00:00"
     * )
     *
     * @var string
     */
    private $createdAt;

    /**
     * @OA\Property(
     *     title="createdAtForHumans",
     *     description="the date when the notification was created, but in human readable form",
     *     format="string",
     *     example="2 days ago"
     * )
     *
     * @var string
     */
    private $updatedAtForHumans;
}
