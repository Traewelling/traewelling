<?php

namespace App\Virtual\RequestBodies;

/**
 * @OA\Schema(
 *     title="StatusUpdateBody",
 *     description="Status Update Body",
 *     @OA\Xml(
 *         name="StatusUpdateBody"
 *     )
 * )
 */
class StatusUpdateBody
{
    /**
     * @OA\Property (
     *     title="body",
     *     maxLength=280,
     *     description="Status-Text to be displayed alongside the checkin",
     *     example="Wow. This train is extremely crowded!",
     *     nullable=true
     * )
     * @var string;
     */
    public $body;

    /**
     * @OA\Property (
     *     ref="#/components/schemas/BusinessEnum"
     * )
     * @var integer
     */
    public $business;

    /**
     * @OA\Property (
     *     ref="#/components/schemas/VisibilityEnum",
     * )
     * @var integer
     */
    public $visibility;
}
