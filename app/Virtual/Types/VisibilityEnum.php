<?php

namespace App\Virtual\Types;
/**
 * @OA\Schema(
 *     title="visibility",
 *     description="What type of visibility (0=public, 1=unlisted, 2=followers, 3=private, 4=authenticated) did the
 *     user specify?",
 *     type="integer",
 *     enum={0,1,2,3,4},
 *     example=0
 * )
 */
class VisibilityEnum
{

}
