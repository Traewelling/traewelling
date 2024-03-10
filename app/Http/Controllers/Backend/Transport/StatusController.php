<?php

namespace App\Http\Controllers\Backend\Transport;

use App\Http\Controllers\Backend\Support\MentionHelper;
use App\Http\Controllers\Controller;
use App\Models\Station;
use App\Models\Status;
use App\Models\Stopover;

abstract class StatusController extends Controller
{

    /**
     * @param Status $status
     *
     * @return Station|null
     */
    public static function getNextStationForStatus(Status $status): ?Station {
        return $status->checkin->trip->stopovers
            ->filter(function(Stopover $stopover) {
                return $stopover->arrival->isFuture();
            })
            ->sortBy('arrival') //sort by real time and if not available by planned time
            ->first()?->station;
    }

    /**
     * Prepare the body for printing in the frontend.
     *
     * @param Status $status
     *
     * @return string
     */
    public static function getPrintableEscapedBody(Status $status): string {
        //Get the body with mention links (this string is already escaped)
        $body = MentionHelper::getBodyWithMentionLinks($status);

        //Replace multiple line breaks with two line breaks
        $body = preg_replace('~(\R{2})\R+~', '$1', $body);

        //Replace line breaks with <br> tags
        return nl2br($body);
    }
}
