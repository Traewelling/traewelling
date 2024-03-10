<?php

namespace App\Virtual\Models;


/**
 * @OA\Schema(
 *     title="UserProfileSettings",
 *     description="Model for all user profile settings",
 *     @OA\Xml(
 *         name="UserProfileSettings"
 *     )
 * )
 */
class UserProfileSettings
{
    /**
     * @OA\Property(
     *     title="username",
     *     description="username",
     *     example="Gertrud123"
     * )
     *
     * @var string
     **/
    private $username;

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
     *     title="privateProfile",
     *     description="Is the profile private?",
     *     format="boolean",
     *     example=false
     * )
     *
     * @var bool
     */
    private $privateProfile;

    /**
     * @OA\Property(
     *     title="preventIndex",
     *     description="Did the user choose to prevent search engines from indexing their profile?",
     *     format="boolean",
     *     example=false
     * )
     *
     * @var bool
     */
    private $preventIndex;

    /**
     * @OA\Property(
     *     title="defaultStatusVisibility",
     *     description="Default status visibility for new statuses",
     *      ref="#/components/schemas/VisibilityEnum"
     * )
     *
     * @var integer
     */
    private $defaultStatusVisibility;

    /**
     * @OA\Property(
     *     title="privacyHideDays",
     *     description="Number of days after which a status is hidden from the public",
     *     format="int64",
     *     example=1,
     *     nullable=true
     * )
     */
    private $privacyHideDays;

    /**
     * @OA\Property(
     *     title="password",
     *     description="Does the user have a password set?",
     *     format="boolean",
     *     example=true
     * )
     *
     * @var bool
     */
    private $password;

    /**
     * @OA\Property (
     *     title="email",
     *     description="The email address of the user",
     *     nullable=true,
     *     example="gertrud@example.com"
     * )
     *
     * @var string
     */
    private $email;

    /**
     * @OA\Property (
     *     title="emailVerified",
     *     description="Is the email address verified?",
     *     format="boolean",
     *     example="https://chaos.social/@traewelling"
     * )
     *
     * @var bool
     */
    private $emailVerified;

    /**
     * @OA\Property (
     *     title="profilePictureSet",
     *     description="Has the user set a profile picture other then the default one?",
     *     type="boolean",
     *     example=false
     * )
     *
     * @var bool
     */
    private $profilePictureSet;

    /**
     * @OA\Property (
     *     title="mastodon",
     *     description="Mastodon URL of user",
     *     example="https://chaos.social/@traewelling"
     * )
     *
     * @var string
     */
    private $mastodon;

    /**
     * @OA\Property(
     *     title="mastodonVisibility",
     *     description="Post visibility for future posts to Mastodon",
     *      ref="#/components/schemas/VisibilityEnum"
     * )
     *
     * @var integer
     */
    private $mastodonVisibility;
}
