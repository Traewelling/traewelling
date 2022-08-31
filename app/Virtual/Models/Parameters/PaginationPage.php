<?php

namespace App\Virtual\Models\Parameters;

/**
 * @OA\Schema(
 *     title="PaginationPage",
 *     description="pagination links",
 *     @OA\Xml(
 *         name="PaginationPage"
 *     )
 * )
 */
class PaginationPage
{
    /**
     * @OA\Parameter (
     *     name="page",
     *     description="Page of pagination",
     *     required=false,
     *     in="query",
     *     @OA\Schema(type="integer")
     * )
     * @var int;
     */
    public $page;
}
