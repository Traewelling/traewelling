<?php

namespace App\Http\Controllers\Backend\Transport;

use App\Enum\StatusVisibility;
use App\Http\Controllers\Backend\Support\MentionHelper;
use App\Http\Controllers\Controller;
use App\Models\Station;
use App\Models\Status;
use App\Models\Stopover;
use App\Models\User;
use Closure;
use Illuminate\Database\Eloquent\Builder;

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

    /**
     * @param User|null $viewingUser The user who is viewing the statuses (null = guest)
     *
     * @return Closure
     */
    public static function filterStatusVisibility(?User $viewingUser = null): Closure {
        return function(Builder $query) use ($viewingUser) {
            //Visibility checks: One of the following options must be true

            //Option 1: User is public AND status is public
            $query->where(function(Builder $query) use ($viewingUser) {
                $query->where('users.private_profile', 0)
                      ->whereIn('visibility', [StatusVisibility::PUBLIC->value] + ($viewingUser !== null ? [StatusVisibility::AUTHENTICATED->value] : []));
            });

            if ($viewingUser !== null) {
                //Option 2: Status is from oneself
                $query->orWhere('users.id', $viewingUser->id);

                //Option 3: Status is from a followed BUT not unlisted or private
                $query->orWhere(function(Builder $query) use ($viewingUser) {
                    $query->whereIn('users.id', $viewingUser->follows()->select('follow_id'))
                          ->whereNotIn('statuses.visibility', [
                              StatusVisibility::UNLISTED->value,
                              StatusVisibility::PRIVATE->value,
                          ]);
                });
            }
        };
    }
}
