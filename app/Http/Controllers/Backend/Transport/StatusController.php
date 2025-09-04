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

abstract class StatusController extends Controller {

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
        return function(Builder $q) use ($viewingUser) {
            $vis = $viewingUser
                ? [StatusVisibility::PUBLIC->value, StatusVisibility::AUTHENTICATED->value]
                : [StatusVisibility::PUBLIC->value];

            $q->where(function(Builder $or) use ($viewingUser, $vis) {
                //Option 1: User is public AND status is public
                $or->where(function(Builder $qq) use ($vis) {
                    $qq->where('users.private_profile', 0)
                       ->whereIn('statuses.visibility', $vis);
                });

                if($viewingUser) {
                    //Option 2: Status is from oneself
                    $or->orWhere('statuses.user_id', $viewingUser->id);

                    //Option 3: Status is from a followed BUT not unlisted or private or trusted users only
                    $or->orWhere(function(Builder $qq) use ($viewingUser) {
                        $qq->whereIn('statuses.visibility', [
                            StatusVisibility::PUBLIC->value,
                            StatusVisibility::AUTHENTICATED->value,
                        ])->whereExists(function($f) use ($viewingUser) {
                            $f->selectRaw('1')
                              ->from('follows')
                              ->whereColumn('follows.follow_id', 'users.id')
                              ->where('follows.user_id', $viewingUser->id);
                        });
                    });

                    // Option 4: trusted + author trusts viewer (not expired)
                    $or->orWhere(function(Builder $qq) use ($viewingUser) {
                        $qq->where('statuses.visibility', StatusVisibility::TRUSTED->value)
                           ->whereExists(function($t) use ($viewingUser) {
                               $t->selectRaw('1')
                                 ->from('trusted_users')
                                 ->whereColumn('trusted_users.user_id', 'statuses.user_id')
                                 ->where('trusted_users.trusted_id', $viewingUser->id)
                                 ->where(function($exp) {
                                     $exp->whereNull('trusted_users.expires_at')
                                         ->orWhere('trusted_users.expires_at', '>', now());
                                 });
                           });
                    });
                }
            });
        };
    }

    public static function filterNotMuted(?User $viewer): Closure {
        return function(Builder $q) use ($viewer) {
            if(!$viewer) {
                return; // unauthenticated: no filter needed
            }
            $q->whereNotExists(function($m) use ($viewer) {
                $m->selectRaw('1')
                  ->from('user_mutes')
                  ->whereColumn('user_mutes.muted_id', 'statuses.user_id')
                  ->where('user_mutes.user_id', $viewer->id);
            });
        };
    }

    public static function filterNotBlockedEitherDirection(?User $viewer): Closure {
        return function(Builder $q) use ($viewer) {
            if(!$viewer) {
                return; // unauthenticated: no filter needed
            }
            // viewer has blocked author
            $q->whereNotExists(function($b1) use ($viewer) {
                $b1->selectRaw('1')
                   ->from('user_blocks')
                   ->whereColumn('user_blocks.blocked_id', 'statuses.user_id')
                   ->where('user_blocks.user_id', $viewer->id);
            })
                // author has blocked viewer
              ->whereNotExists(function($b2) use ($viewer) {
                    $b2->selectRaw('1')
                       ->from('user_blocks')
                       ->whereColumn('user_blocks.user_id', 'statuses.user_id')
                       ->where('user_blocks.blocked_id', $viewer->id);
                });
        };
    }

    public static function filterAudienceRestrictions(?User $viewer): Closure {
        return function(Builder $q) use ($viewer) {
            $q->where(self::filterNotMuted($viewer))
              ->where(self::filterNotBlockedEitherDirection($viewer));
        };
    }
}
