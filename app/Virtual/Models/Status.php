<?php

namespace App\Virtual\Models;

use DateTime;

/**
 * @OA\Schema(
 *     title="Status",
 *     description="Status model",
 *     @OA\Xml(
 *         name="Status"
 *     )
 * )
 */
class Status
{
    /**
     * @OA\Property(
     *     title="ID",
     *     description="ID",
     *     format="int64",
     *     example=12345
     * )
     *
     * @var integer
     **/
    private $id;

    /**
     * @OA\Property(
     *     title="body",
     *     description="User defined status text",
     *     example="Hello world!"
     * )
     *
     * @var string;
     */
    public $body;

    /**
     * @OA\Property (
     *     title="type",
     *     description="type of status",
     *     example="HAFAS"
     * )
     *
     * @var string
     */
    private $type;

    /**
     * @OA\Property(
     *     title="user",
     *     description="user id",
     *     format="int64",
     *     example=1
     * )
     *
     * @var integer
     */
    private $user;

    /**
     * @OA\Property (
     *     title="username",
     *     description="username (@-name)",
     *     example="Gertrud123"
     * )
     *
     * @var string
     */
    private $username;

    /**
     * @OA\Property (
     *     title="profilePicture",
     *     description="profile picture URL of user",
     *     example="https://traewelling.de/@Gertrud123/picture"
     * )
     *
     * @var string
     */

    /**
     * @OA\Property (
     *     title="preventIndex",
     *     description="Did the user choose to prevent search engines from indexing their profile?",
     *     type="boolean",
     *     example=false
     * )
     *
     * @var bool
     */
    private $preventIndex;

    /**
     * @OA\Property (
     *     title="business",
     *     description="What type of travel (0=private, 1=business, 2=commute) did the user specify?",
     *     type="integer",
     *     enum={0,1,2},
     *     example=0
     * )
     *
     * @var integer
     */
    private $business;

    /**
     * @OA\Property (
     *     title="visibility",
     *     description="What type of visibility (0=public, 1=unlisted, 2=followers, 3=private, 4=authenticated) did the
     *     user specify?", type="integer", enum={0,1,2,3,4}, example=0
     * )
     *
     * @var integer
     */
    private $visibility;

    /**
     * @OA\Property (
     *     title="likes",
     *     description="How many people have liked this status",
     *     format="int64",
     *     example=12
     * )
     *
     * @var integer
     */
    private $likes;

    /**
     * @OA\Property (
     *     title="liked",
     *     description="Did the currently authenticated user like this status? (if unauthenticated = false)",
     *     type="boolean",
     *     example=true
     * )
     *
     * @var bool
     */
    private $liked;

    /**
     * @OA\Property (
     *     title="createdAt",
     *     description="creation date of this status",
     *     type="string",
     *     format="datetime",
     *     example="2022-07-17T13:37:00+02:00"
     * )
     *
     * @var DateTime
     */
    private $createdAt;

    /**
     * @OA\Property (
     *     title="train",
     *     description="Train model",
     * )
     *
     * @var Train
     */
    private $train;

    /**
     * @OA\Property (
     *     title="event",
     *     description="Event model (nullable)",
     *     nullable=true
     * )
     *
     * @var Event
     */
    private $event;
}
