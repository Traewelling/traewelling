<?php

namespace App\Virtual\Models;


/**
 * @OA\Schema(
 *     title="User",
 *     description="User model",
 *     @OA\Xml(
 *         name="User"
 *     )
 * )
 */
class User
{
    /**
     * @OA\Property(
     *     title="ID",
     *     description="ID",
     *     format="int64",
     *     example=1
     * )
     *
     * @var integer
     **/
    private $id;

    /**
     * @OA\Property(
     *     title="displayName",
     *     description="Display name of the user",
     *     example="Gertrud"
     * )
     *
     * @var string;
     */
    public $displayName;

    /**
     * @OA\Property (
     *     title="username",
     *     description="username of user",
     *     example="Gertrud123"
     * )
     *
     * @var string
     */
    private $username;

    /**
     * @OA\Property(
     *     title="profilePicture",
     *     description="URL of the profile picture of the user",
     *     example="https://traewelling.de/@Gertrud123/picture"
     * )
     *
     * @var integer
     */
    private $profilePicture;

    /**
     * @OA\Property(
     *     title="trainDistance",
     *     description="distance travelled by train in meters",
     *     format="int64",
     *     example=12345
     * )
     *
     * @var integer
     */
    private $trainDistance;

    /**
     * @OA\Property(
     *     title="trainDuration",
     *     description="duration travelled by train in minutes",
     *     format="int64",
     *     example=6
     * )
     *
     * @var integer
     */
    private $trainDuration;

    /**
     * @OA\Property(
     *     title="trainSpeed",
     *     description="average speed travelled by train in meters",
     *     format="float",
     *     example=123.45
     * )
     *
     * @var float
     */
    private $trainSpeed;

    /**
     * @OA\Property(
     *     title="points",
     *     description="Current points of the last 7 days",
     *     format="int64",
     *     example=300
     * )
     *
     * @var integer
     */
    private $points;

    /**
     * @OA\Property (
     *     title="twitterUrl",
     *     description="URL to the Twitter profile of the user",
     *     nullable=true,
     *     example="https://twitter.com/i/user/843458335"
     * )
     *
     * @var string
     */
    private $twitterUrl;

    /**
     * @OA\Property (
     *     title="mastodonUrl",
     *     description="URL to the Mastodon profile of the user",
     *     nullable=true,
     *     example="https://chaos.social/@traewelling"
     * )
     *
     * @var string
     */
    private $mastodonUrl;

    /**
     * @OA\Property (
     *     title="privateProfile",
     *     description="is this profile set to private?",
     *     type="boolean",
     *     example=false
     * )
     *
     * @var bool
     */
    private $privateProfile;

    /**
     * @OA\Property (
     *     title="userInvisibleToMe",
     *     description="Can the currently authenticated user see the statuses of this user?",
     *     type="boolean",
     *     example=false
     * )
     *
     * @var bool
     */
    private $userInvisibleToMe;

    /**
     * @OA\Property (
     *     title="muted",
     *     description="Is this user muted by the currently authenticated user?",
     *     type="boolean",
     *     example=false
     * )
     *
     * @var bool
     */
    private $muted;

    /**
     * @OA\Property (
     *     title="following",
     *     description="Does the currently authenticated user follow this user?",
     *     type="boolean",
     *     example=false
     * )
     *
     * @var bool
     */
    private $following;

    /**
     * @OA\Property (
     *     title="followPending",
     *     description="Is there a currently pending follow request?",
     *     type="boolean",
     *     example=false
     * )
     *
     * @var bool
     */
    private $followPending;

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

}
