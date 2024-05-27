<?php

namespace App\Http\Controllers;

use App\Enum\Business;
use App\Enum\StatusVisibility;
use App\Events\StatusDeleteEvent;
use App\Events\StatusUpdateEvent;
use App\Exceptions\PermissionException;
use App\Exceptions\StatusAlreadyLikedException;
use App\Http\Controllers\API\v1\Controller as APIController;
use App\Http\Controllers\Backend\Support\LocationController;
use App\Models\Event;
use App\Models\Like;
use App\Models\Status;
use App\Models\User;
use App\Notifications\StatusLiked;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * @deprecated Content will be moved to the backend/frontend/API packages soon, please don't add new functions here!
 */
class StatusController extends Controller
{
    /**
     * Authorization in Frontend required! $this->authorize('view', $status);
     *
     * @param int $statusId
     *
     * @return Status
     * @throws HttpException
     * @throws ModelNotFoundException
     * @api v1
     * @frontend
     */
    public static function getStatus(int $statusId): Status {
        return Status::where('id', $statusId)
                     ->with([
                                'event', 'likes', 'user.blockedByUsers', 'user.blockedUsers', 'checkin',
                                'checkin.originStation', 'checkin.destinationStation',
                                'checkin.Trip.stopovers.station',
                            ])
                     ->firstOrFail();
    }

    /**
     * This method returns the current active statuses for all users where the viewer is allowed to see.
     *
     * @return Collection|null
     * @api v1
     * @frontend
     */
    public static function getActiveStatuses(): ?Collection {
        return Status::with([
                                'event', 'likes', 'user.blockedByUsers', 'user.blockedUsers', 'user.followers',
                                'checkin.originStation', 'checkin.destinationStation',
                                'checkin.trip.stopovers.station',
                                'checkin.trip.polyline',
                            ])
                     ->whereHas('checkin', function($query) {
                         $query->where('departure', '<', now())
                               ->where('arrival', '>', now());
                     })
                     ->get()
                     ->filter(function(Status $status) {
                         return Gate::allows('view', $status) && $status->visibility !== StatusVisibility::UNLISTED;
                     })
                     ->sortByDesc(function(Status $status) {
                         return $status->checkin->departure;
                     })->values();
    }

    public static function getLivePositions(): array {
        $statuses = self::getActiveStatuses();

        $result = [];
        foreach ($statuses as $status) {
            $position = LocationController::forStatus($status)->calculateLivePosition();
            if ($position) {
                $result[] = $position;
            }
        }
        return $result;
    }

    public static function getLivePositionForStatus(string $ids): array {
        $ids = explode(',', $ids);

        $statuses = Status::with([
                                     'user.blockedByUsers', 'user.blockedUsers', 'user.followers',
                                     'checkin.originStation', 'checkin.destinationStation',
                                     'checkin.Trip.stopovers.station',
                                     'checkin.Trip.polyline',
                                 ])
                          ->whereIn('id', $ids)
                          ->get()
                          ->filter(function(Status $status) {
                              return Gate::allows('view', $status) && $status->visibility !== StatusVisibility::UNLISTED;
                          })
                          ->values();

        $result = [];
        foreach ($statuses as $status) {
            $position = LocationController::forStatus($status)->calculateLivePosition();
            if ($position) {
                $result[] = $position;
            }
        }

        return $result;
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

        StatusDeleteEvent::dispatch($status);

        return true;
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
        if ($user->cannot('like', $status)) {
            throw new PermissionException();
        }

        if ($status->likes->contains('user_id', $user->id)) {
            throw new StatusAlreadyLikedException($user, $status);
        }

        $like = Like::create([
                                 'user_id'   => $user->id,
                                 'status_id' => $status->id
                             ]);

        if (!$status->user->mutedUsers->contains('id', $user->id)) {
            $status->user->notify(new StatusLiked($like));
        }

        StatusUpdateEvent::dispatch($status->refresh());

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

        StatusUpdateEvent::dispatch(Status::find($statusId)->first());
    }

    public static function usageByDay(Carbon $date): int {
        return Status::where("created_at", ">=", $date->copy()->startOfDay())
                     ->where("created_at", "<=", $date->copy()->endOfDay())
                     ->count();
    }

    /**
     * @param Event $event
     *
     * @return array
     */
    public static function getStatusesByEvent(Event $event): array {
        $statuses = $event->statuses()
                          ->with([
                                     'user.blockedUsers', 'checkin.originStation',
                                     'checkin.destinationStation', 'checkin.Trip.stopovers', 'event', 'likes',
                                 ])
                          ->select('statuses.*')
                          ->join('users', 'statuses.user_id', '=', 'users.id')
                          ->join('train_checkins', 'statuses.id', '=', 'train_checkins.status_id')
                          ->where(function(Builder $query) {
                              //Visibility checks: One of the following options must be true

                              //Option 1: User is public AND status is public
                              $query->where(function(Builder $query) {
                                  $query->where('users.private_profile', 0)
                                        ->whereIn('visibility', [
                                            StatusVisibility::PUBLIC->value,
                                            StatusVisibility::AUTHENTICATED->value
                                        ]);
                              });

                              if (auth()->check()) {
                                  //Option 2: Status is from oneself
                                  $query->orWhere('users.id', auth()->id());

                                  //Option 3: Status is from a followed BUT not unlisted or private
                                  $query->orWhere(function(Builder $query) {
                                      $query->whereIn('users.id', auth()->user()->follows()->select('follow_id'))
                                            ->whereNotIn('visibility', [
                                                StatusVisibility::UNLISTED->value,
                                                StatusVisibility::PRIVATE->value,
                                            ]);
                                  });
                              }
                          })
                          ->orderBy('train_checkins.departure', 'desc');

        if (auth()->check()) {
            $statuses->whereNotIn('statuses.user_id', auth()->user()?->mutedUsers()->select('muted_id'))
                     ->whereNotIn('statuses.user_id', auth()->user()?->blockedUsers()->select('blocked_id'))
                     ->whereNotIn('statuses.user_id', auth()->user()?->blockedByUsers()->select('user_id'));
        }

        return [
            'event'    => $event,
            'statuses' => $statuses,
        ];
    }

    public static function getFutureCheckins(): Paginator {
        return auth()->user()->statuses()
                     ->with([
                                'user', 'checkin.originStation', 'checkin.destinationStation',
                                'checkin.Trip', 'event',
                            ])
                     ->orderByDesc('created_at')
                     ->whereHas('checkin', function($query) {
                         $query->where('departure', '>=', date('Y-m-d H:i:s', strtotime("+20min")));
                     })
                     ->simplePaginate(15);
    }

    public static function createStatus(
        User             $user,
        Business         $business,
        StatusVisibility $visibility,
        string           $body = null,
        Event            $event = null
    ): Status {
        if ($event !== null && !Carbon::now()->isBetween($event->begin, $event->end)) {
            Log::info('Event checkin was prevented because the event is not active anymore', [
                'event' => $event->only(['id', 'name', 'begin', 'end']),
                'user'  => $user->only(['id', 'username']),
            ]);
            $event = null;
        }

        return Status::create([
                                  'user_id'    => $user->id,
                                  'body'       => $body,
                                  'business'   => $business,
                                  'visibility' => $visibility,
                                  'event_id'   => $event?->id,
                                  'client_id'  => APIController::getCurrentOAuthClient()?->id,
                              ]);
    }
}
