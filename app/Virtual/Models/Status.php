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
     * @OA\Property(
     *     title="bodyMentions",
     *     description="Mentions in the status body",
     *     type="array",
     *     @OA\Items(
     *         ref="#/components/schemas/MentionDto"
     *     )
     * )
     *
     * @var array
     */
    public $bodyMentions;

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
     *     ref="#/components/schemas/BusinessEnum"
     * )
     *
     * @var integer
     */
    private $business;

    /**
     * @OA\Property (
     *      ref="#/components/schemas/VisibilityEnum"
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
     *     title="isLikable",
     *     description="Do the author of this status and the currently authenticated user allow liking of statuses? Only show the like UI if set to true",
     *     type="boolean",
     *     example=true
     * )
     *
     * @var bool
     */
    private $isLikable;

    /**
     * @OA\Property (
     *     title="client",
     *     description="Client model",
     * )
     *
     * @var Client
     */
    private $client;

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
     * @var Trip
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

    /**
     * @OA\Property (
     *     title="userDetails",
     *     description="User model",
     * )
     *
     * @var User
     */
    private $userDetails;
}
