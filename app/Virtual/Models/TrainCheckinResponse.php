<?php

namespace App\Virtual\Models;

use Carbon\Carbon;

/**
 * @OA\Schema(
 *     title="TrainCheckinResponse",
 *     @OA\Xml(
 *         name="TrainCheckinResponse"
 *     )
 * )
 */
class TrainCheckinResponse
{
    /**
     * @OA\Property(
     *     title="status",
     *     description="StatusModel of the created status",
     *     example="",
     * )
     *
     * @var string
     */
    private $status;

    /**
     * @OA\Property (
     *     title="points",
     *     description="points and reasons for the points",
     *     ref="#/components/schemas/Points"
     * )
     *
     * @var string
     */
    private $points;

    /**
     * @OA\Property (
     *     title="alsoOnThisconnection",
     *     description="Statuses of other people on this connection",
     *     @OA\Items(
     *     ref="#/components/schemas/Status"
     *     )
     * )
     *
     * @var array
     */
    private $alsoOnThisConnection;

}
