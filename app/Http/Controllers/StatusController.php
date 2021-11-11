<?php

namespace App\Http\Controllers;

use App\Enum\Business;
use App\Enum\StatusVisibility;
use App\Exceptions\PermissionException;
use App\Exceptions\StatusAlreadyLikedException;
use App\Models\Event;
use App\Models\Like;
use App\Models\Status;
use App\Models\User;
use App\Notifications\StatusLiked;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use InvalidArgumentException;
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * @deprecated Content will be moved to the backend/frontend/API packages soon, please don't add new functions here!
 */
class StatusController extends Controller
{
    /**
     * @param int $statusId
     *
     * @return Status
     * @throws HttpException
     * @throws ModelNotFoundException
     * @api v1
     * @frontend
     */
    public static function getStatus(int $statusId): Status {
        $status = Status::where('id', $statusId)->with('user',
                                                       'trainCheckin',
                                                       'trainCheckin.Origin',
                                                       'trainCheckin.Destination',
                                                       'trainCheckin.HafasTrip',
                                                       'event')->withCount('likes')->firstOrFail();

        if (request()?->user()->can('view', $status)) {
            return $status;
        }

        abort(403, "Status invisible to you.");
    }

    /**
     * This Method returns the current active status(es) for all users or a specific user.
     *
     * @param null $userId UserId to get the current active status for a user. Defaults to null.
     * @param bool $array  This parameter is a temporary solution until the frontend is no more dependend on blade.
     *
     * @return Status|array|Builder|Model|object|null
     * @api v1
     * @frontend
     */
    public static function getActiveStatuses($userId = null, bool $array = true) {
        if ($userId === null) {
            $statuses = Status::with([
                                         'likes',
                                         'user',
                                         'trainCheckin.Origin',
                                         'trainCheckin.Destination',
                                         'trainCheckin.HafasTrip.polyline',
                                         'trainCheckin.HafasTrip.stopoversNEW.trainStation',
                                         'event'
                                     ])
                              ->whereHas('trainCheckin', function($query) {
                                  $query->where('departure', '<', date('Y-m-d H:i:s'))
                                        ->where('arrival', '>', date('Y-m-d H:i:s'));
                              })
                              ->get()
                              ->filter(function(Status $status) {
                                  return request()?->user()->can('view', $status);
                              })
                              ->sortByDesc(function(Status $status) {
                                  return $status->trainCheckin->departure;
                              })->values();
        } else {
            $status = Status::with([
                                       'user',
                                       'trainCheckin.Origin',
                                       'trainCheckin.Destination',
                                       'trainCheckin.HafasTrip.polyline',
                                       'event'
                                   ])
                            ->whereHas('trainCheckin', function($query) {
                                $query->where('departure', '<', date('Y-m-d H:i:s'))
                                      ->where('arrival', '>', date('Y-m-d H:i:s'));
                            })
                            ->where('user_id', $userId)
                            ->first();
            if (!request()?->user()->can('view', $status)) {
                return null;
            }
            return $status;
            //This line is important since we're using this method for two different purposes and I forgot that.
        }
        if ($statuses === null) {
            return null;
        }
        $polylines = $statuses->map(function($status) {
            return json_encode($status->trainCheckin->getMapLines());
        });
        if ($array) {
            return ['statuses' => $statuses->toArray(), 'polylines' => $polylines];
        }

        return ['statuses' => $statuses, 'polylines' => $polylines];
    }

    /**
     * @param User $user
     * @param int  $statusId
     *
     * @return bool|null
     * @throws PermissionException|ModelNotFoundException
     */
    public static function DeleteStatus(User $user, int $statusId): ?bool {
        $status = Status::find($statusId);

        if ($status === null) {
            throw new ModelNotFoundException();
        }
        if ($user->id != $status->user->id) {
            throw new PermissionException();
        }
        $status->delete();
        return true;
    }

    /**
     * @param User        $user
     * @param int         $statusId
     * @param string|null $body
     * @param int         $business
     * @param int         $visibility
     *
     * @return Status
     * @throws PermissionException
     * @api v1
     */
    public static function EditStatus(
        User   $user,
        int    $statusId,
        string $body = null,
        int    $business = Business::PRIVATE,
        int    $visibility = StatusVisibility::PUBLIC
    ): Status {
        $status = Status::findOrFail($statusId);

        if ($user->id !== $status->user->id) {
            throw new PermissionException();
        }

        $status->update([
                            'body'       => $body,
                            'business'   => $business,
                            'visibility' => $visibility,
                        ]);
        return $status;
    }

    /**
     * Create a Statuslike for a given User
     *
     * @param User   $user
     * @param Status $status
     *
     * @return Like
     * @throws StatusAlreadyLikedException|PermissionException
     */
    public static function createLike(User $user, Status $status): Like {

        if ($user->cannot('view', $status)) {
            throw new PermissionException();
        }

        if ($status->likes->contains('user_id', $user->id)) {
            throw new StatusAlreadyLikedException($user, $status);
        }

        $like = Like::create([
                                 'user_id'   => $user->id,
                                 'status_id' => $status->id
                             ]);
        $status->user->notify(new StatusLiked($like));
        return $like;
    }

    /**
     * @param User $user
     * @param int  $statusId
     *
     * @return void
     * @throws InvalidArgumentException
     */
    public static function destroyLike(User $user, int $statusId): void {
        $like = $user->likes()->where('status_id', $statusId)->first();
        if ($like == null) {
            throw new InvalidArgumentException(__('controller.status.like-not-found'));
        }
        $like->delete();
    }

    public static function getLikes($statusId) {
        return Status::findOrFail($statusId)->likes()->with('user')->simplePaginate(15);
    }

    public static function usageByDay(Carbon $date): int {
        return Status::where("created_at", ">=", $date->copy()->startOfDay())
                     ->where("created_at", "<=", $date->copy()->endOfDay())
                     ->count();
    }

    /**
     * @param string|null $slug
     * @param int|null    $id
     *
     * @return array
     */
    public static function getStatusesByEvent(?string $slug, ?int $id): array {
        if ($slug != null) {
            $event = Event::where('slug', $slug)->firstOrFail();
        }
        if ($id != null) {
            $event = Event::findOrFail($id);
        }


        $statuses = $event->statuses()
                          ->with('user')
                          ->select('statuses.*')
                          ->join('users', 'statuses.user_id', '=', 'users.id')
                          ->where(function($query) {
                              $query->where('users.private_profile', 0)
                                    ->where('visibility', StatusVisibility::PUBLIC);
                              if (auth()->check()) {
                                  $query->orWhere('users.id', auth()->user()->id)
                                        ->orWhere(function($query) {
                                            $query->where('visibility', StatusVisibility::FOLLOWERS)
                                                  ->whereIn('users.id', auth()->user()->follows()->select('follow_id'))
                                                  ->orWhere('visibility', StatusVisibility::PUBLIC);
                                        });
                              }
                          });

        if (auth()->check()) {
            $statuses->whereNotIn('user_id', auth()->user()->mutedUsers()->select('muted_id'));
        }

        return ['event' => $event, 'statuses' => $statuses->simplePaginate(15)];
    }

    public static function getFutureCheckins(): Paginator {
        return auth()->user()->statuses()
                     ->with('user',
                            'trainCheckin',
                            'trainCheckin.Origin',
                            'trainCheckin.Destination',
                            'trainCheckin.HafasTrip',
                            'event')
                     ->orderBy('created_at', 'DESC')
                     ->whereHas('trainCheckin', function($query) {
                         $query->where('departure', '>=', date('Y-m-d H:i:s', strtotime("+20min")));
                     })->simplePaginate(15);
    }

    public static function createStatus(
        User   $user,
        int    $business,
        int    $visibility,
        string $body = null,
        int    $eventId = null,
        string $type = "hafas"
    ): Status {
        $event = null;
        if ($eventId !== null) {
            $event = Event::find($eventId);
            if (!Carbon::now()->isBetween($event?->begin, $event?->end)) {
                $event = null;
            }
        }

        return Status::create([
                                  'user_id'    => $user->id,
                                  'body'       => $body,
                                  'business'   => $business,
                                  'visibility' => $visibility,
                                  'type'       => $type,
                                  'event'      => $event?->id
                              ]);
    }
}
