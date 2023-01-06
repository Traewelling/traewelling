<?php

namespace App\Virtual\Types;
/**
 * @OA\Schema(
 *     title="category",
 *     description="Category of transport. ",
 *     type="string",
 *     enum={"nationalExpress", "national", "regionalExp", "regional", "suburban", "bus", "ferry", "subway",
 *     "tram", "taxi"},
 *     example="suburban"
 * )
 */
class TrainCategoryEnum
{

}
