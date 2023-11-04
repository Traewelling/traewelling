<?php

namespace App\Virtual\Models;


/**
 * @OA\Schema(
 *     title="LeaderboardUser",
 *     @OA\Xml(
 *         name="LeaderboardUser"
 *     )
 * )
 */
class LeaderboardUser
{
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
     *     title="points",
     *     description="Current points of the last 7 days",
     *     format="int64",
     *     example=300
     * )
     *
     * @var integer
     */
    private $points;
}
