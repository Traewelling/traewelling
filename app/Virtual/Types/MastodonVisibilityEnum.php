<?php

namespace App\Virtual\Types;
/**
 * @OA\Schema(
 *     title="visibility",
 *     description="What type of visibility (0=public, 1=unlisted, 2=followers, 3=private) did the user specify for
 *     future posts to Mastodon? Some instances such as chaos.social discourage bot posts on public timelines.",
 *     type="integer",
 *     enum={0,1,2,3},
 *     example=1
 * )
 */
class MastodonVisibilityEnum
{

}
