<?php

namespace App\Virtual\Models;

/**
 * @OA\Schema(
 *     title="Webhook",
 *     description="Webhook model",
 *     @OA\Xml(
 *         name="Webhook"
 *     )
 * )
 */
class Webhook {
    /**
     * @OA\Property(
     *     title="ID",
     *     description="ID",
     *     format="int",
     *     example=12345
     * )
     *
     * @var integer
     **/
    private $id;


    /**
     * @OA\Property(
     *     title="ClientID",
     *     description="ID of the client which created this webhook",
     *     format="int",
     *     example=12345
     * )
     *
     * @var integer
     **/
    private $clientId;


    /**
     * @OA\Property(
     *     title="UserID",
     *     description="ID of the user which created this webhook",
     *     format="int",
     *     example=12345
     * )
     *
     * @var integer
     **/
    private $userId;


    /**
     * @OA\Property(
     *     title="url",
     *     description="URL where the webhook gets sent to",
     *     example="https://example.com/webhook"
     * )
     *
     * @var string;
     */
    private $url;


    /**
     * @OA\Property(
     *     title="createdAt",
     *     description="creation date of this webhook",
     *     type="string",
     *     format="datetime",
     *     example="2022-07-17T13:37:00+02:00"
     * )
     *
     * @var DateTime
     */
    private $createdAt;

    /**
     * @OA\Property(
     *     title="events",
     *     description="array of events this webhook receives",
     *     type="array",
     *     @OA\Items()
     * )
     *
     */
    private $events;
}
