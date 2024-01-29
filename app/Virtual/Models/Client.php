<?php

namespace App\Virtual\Models;

/**
 * @OA\Schema(
 *     title="Client",
 *     description="Client model",
 *     @OA\Xml(
 *         name="Client"
 *     )
 * )
 */
class Client
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
     *     description="Name of client",
     *     example="Träwelling App"
     * )
     *
     * @var string
     */
    private $name;

    /**
     * @OA\Property (
     *     title="privacyPolicyUrl",
     *     description="URL to privacy policy",
     *     example="https://traewelling.de/privacy-policy"
     * )
     *
     * @var string
     */
    private $privacyPolicyUrl;
}
