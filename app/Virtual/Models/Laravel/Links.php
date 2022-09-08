<?php

namespace App\Virtual\Models\Laravel;
/**
 * @OA\Schema(
 *     title="Links",
 *     description="pagination links",
 *     @OA\Xml(
 *         name="Links"
 *     )
 * )
 */
class Links
{
    /**
     * @OA\Property (
     *     title="first",
     *     description="URL to first page of this pagination",
     *     type="string",
     *     format="uri",
     *     example="https://traewelling.de/api/v1/ENDPOINT?page=1",
     *     nullable=true
     * )
     * @var string;
     */
    private $first;
    /**
     * @OA\Property (
     *     title="last",
     *     description="URL to last page of this pagination (mostly null)",
     *     type="string",
     *     format="uri",
     *     example=null,
     *     nullable=true
     * )
     * @var string;
     */
    private $last;
    /**
     * @OA\Property (
     *     title="prev",
     *     description="URL to previous page of this pagination (mostly null)",
     *     type="string",
     *     format="uri",
     *     example="https://traewelling.de/api/v1/ENDPOINT?page=1",
     *     nullable=true
     * )
     * @var string;
     */
    private $prev;
    /**
     * @OA\Property (
     *     title="next",
     *     description="URL to next page of this pagination (mostly null)",
     *     type="string",
     *     format="uri",
     *     example="https://traewelling.de/api/v1/ENDPOINT?page=2",
     *     nullable=true
     * )
     * @var string;
     */
    private $next;

}
