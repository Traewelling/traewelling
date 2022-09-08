<?php

namespace App\Virtual\Models\Laravel;
/**
 * @OA\Schema(
 *     title="Meta",
 *     description="Pagination meta data",
 *     @OA\Xml(
 *         name="PaginationMeta"
 *     )
 * )
 */
class PaginationMeta
{
    /**
     * @OA\Property (
     *     title="current_page",
     *     description="currently displayed page in this pagination",
     *     type="integer",
     *     example=2,
     * )
     * @var int;
     */
    private $current_page;
    /**
     * @OA\Property (
     *     title="from",
     *     description="The first element on this page is the nth element of the query",
     *     type="integer",
     *     example=16,
     * )
     * @var int;
     */
    private $from;
    /**
     * @OA\Property (
     *     title="path",
     *     description="The path of this pagination",
     *     type="string",
     *     format="url",
     *     example="https://traewelling.de/api/v1/ENDPOINT"
     * )
     * @var string;
     */
    private $path;
    /**
     * @OA\Property (
     *     title="per_page",
     *     description="the amount of items per page in this pagination",
     *     type="integer",
     *     example=15,
     * )
     * @var int;
     */
    private $per_page;
    /**
     * @OA\Property (
     *     title="to",
     *     description="The last element on this page is the nth element of the query",
     *     type="integer",
     *     example=30,
     * )
     * @var int;
     */
    private $to;
}
