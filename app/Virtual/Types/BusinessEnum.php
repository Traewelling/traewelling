<?php

namespace App\Virtual\Types;
/**
 * @OA\Schema(
 *     title="Business",
 *     description="What type of travel (0=private, 1=business, 2=commute) did the user specify?",
 *     type="integer",
 *     enum={0,1,2},
 *     example=0,
 * )
 */
class BusinessEnum
{

}
