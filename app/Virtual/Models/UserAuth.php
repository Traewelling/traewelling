<?php

namespace App\Virtual\Models;


use App\Dto\Transport\Station;

/**
 * @OA\Schema(
 *     title="UserAuth",
 *     description="User auth model",
 *     @OA\Xml(
 *         name="UserAuth"
 *     )
 * )
 */
class UserAuth
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
     *     title="privacyHideDays",
     *     description="Hide all statuses after x days",
     *     example=3,
     *     nullable=true
     * )
     *
     * @var integer
     */
    private $privacyHideDays;

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
     *     title="role",
     *     description="The permission-role of a user. Distinguishes access level to certain features.",
     *     example=0
     * )
     *
     * @var integer
     */
    private $role;

    /**
     * @OA\Property (
     *     title="home",
     *     description="The specified 'home'-station of this user",
     *     nullable=true
     * )
     *
     * @var Station
     */
    private $home;

    /**
     * @OA\Property(
     *     title="language",
     *     description="what is the specified language of this user",
     *     example="en",
     *     nullable=true
     * )
     *
     * @var string;
     */
    public $language;


}
