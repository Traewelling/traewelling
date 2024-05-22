<?php

namespace App\Virtual\Models;


/**
 * @OA\Schema(
 *     title="LightUser",
 *     description="User model with just basic information",
 *     @OA\Xml(
 *         name="LightUser"
 *     )
 * )
 */
class LightUser
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
