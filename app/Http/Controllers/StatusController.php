<?php

namespace App\Http\Controllers;

use App\Enum\Business;
use App\Enum\StatusVisibility;
use App\Events\StatusUpdateEvent;
use App\Exceptions\RateLimitExceededException;
use App\Exceptions\StatusAlreadyLikedException;
use App\Http\Controllers\API\v1\Controller as APIController;
use App\Http\Controllers\Backend\Support\LocationController;
use App\Models\Event;
use App\Models\Follow;
use App\Models\Like;
use App\Models\Status;
use App\Models\User;
use App\Notifications\StatusLiked;
use Carbon\Carbon;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
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
     *
     * @throws HttpException
     * @throws ModelNotFoundException
     *
     * @api v1
     *
     * @frontend
     */
    public static function getStatus(int $statusId): Status
    {
        return Status::where('id', $statusId)
            ->with([
                'event.station',
                'likes',
                'mentions',
                'client',
                'user.blockedByUsers',
                'user.blockedUsers',
                'createdByUser',
                'checkin',
                'checkin.statusTags',
                'tags',
                'checkin.originStopover.station',
                'checkin.destinationStopover.station',
                'checkin.trip.stopovers.station',
                'checkin.trip.operator',
                'checkin.trip.motisSourceLicense',
            ])
            ->firstOrFail();
    }

    /**
     * This method returns the current active statuses for all users where the viewer is allowed to see.
     *
     * @api v1
     *
     * @frontend
     */
    public static function getActiveStatuses(): ?Collection
    {
        return Status::with([
            'event',
            'likes',
            'user.blockedByUsers',
            'user.blockedUsers',
            'user.followers',
            'createdByUser',
            'checkin.originStopover.station',
            'checkin.destinationStopover.station',
            'checkin.trip.stopovers.station',
            'checkin.trip.stopovers.stationIdentifier',
            'checkin.trip.stopovers.routeSegment',
            'checkin.trip.polyline',
            'tags',
        ])
            ->join('train_checkins', 'statuses.id', '=', 'train_checkins.status_id')
            ->where('train_checkins.departure', '>', now()->subHours(config('trwl.max_journey_hours'))) // to reduce the amount of data the database has to process
            ->where('train_checkins.departure', '<', now())
            ->where('train_checkins.arrival', '>', now())
            ->select('statuses.*')
            ->get()
            ->filter(function (Status $status) {
                return Gate::allows('view', $status) && $status->visibility !== StatusVisibility::UNLISTED;
            })
            ->reject(fn (Status $status) => $status->checkin === null)
            ->sortByDesc(function (Status $status) {
                return $status->checkin->departure;
            })->values();
    }

    public static function getLivePositions(): array
    {
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

    public static function getLivePositionForStatus(string $ids): array
    {
        $ids = explode(',', $ids);

        $statuses = Status::with([
            'user.blockedByUsers',
            'user.blockedUsers',
            'user.followers',
            'createdByUser',
            'checkin.originStopover.station',
            'checkin.destinationStopover.station',
            'checkin.trip.stopovers.station',
            'checkin.trip.polyline',
        ])
            ->whereIn('id', $ids)
            ->get()
            ->filter(function (Status $status) {
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
     * @throws ModelNotFoundException
     * @throws AuthorizationException User is not allowed to delete this status
     */
    public static function DeleteStatus(User $user, int $statusId): ?bool
    {
        $status = Status::findOrFail($statusId); // throws ModelNotFoundException
        Gate::forUser($user)->authorize('delete', $status);
        $status->delete();

        return true;
    }

    /**
     * Create a StatusLike for a given User
     *
     *
     * @throws StatusAlreadyLikedException
     * @throws AuthorizationException User is not allowed to like this status
     * @throws RateLimitExceededException
     */
    public static function createLike(User $user, Status $status): Like
    {
        self::likeRateLimiter($user, $status->user);
        Gate::forUser($user)->authorize('like', $status);

        if ($status->likes->contains('user_id', $user->id)) {
            throw new StatusAlreadyLikedException($user, $status);
        }

        $like = Like::updateOrCreate([
            'user_id' => $user->id,
            'status_id' => $status->id,
        ]);

        if (!$status->user->mutedUsers->contains('id', $user->id)) {
            $status->user->notify(new StatusLiked($like));
        }

        StatusUpdateEvent::dispatch($status->refresh());

        return $like;
    }

    /**
     * @throws InvalidArgumentException
     */
    public static function destroyLike(User $user, int $statusId): void
    {
        $like = $user->likes()->where('status_id', $statusId)->first();
        if ($like == null) {
            throw new InvalidArgumentException(__('controller.status.like-not-found'));
        }
        $like->delete();

        StatusUpdateEvent::dispatch(Status::find($statusId));
    }

    public static function usageByDay(Carbon $date): int
    {
        return Status::where('created_at', '>=', $date->copy()->startOfDay())
            ->where('created_at', '<=', $date->copy()->endOfDay())
            ->count();
    }

    public static function getStatusesByEvent(Event $event): array
    {
        $statuses = $event->statuses()
            ->with([
                'user.blockedUsers', 'createdByUser', 'checkin.originStopover.station',
                'checkin.destinationStopover.station', 'checkin.trip.stopovers', 'event', 'likes', 'tags',
            ])
            ->select('statuses.*')
            ->join('users', 'statuses.user_id', '=', 'users.id')
            ->join('train_checkins', 'statuses.id', '=', 'train_checkins.status_id')
            ->where(Backend\Transport\StatusController::filterStatusVisibility(auth()->user()))
            ->orderBy('train_checkins.departure', 'desc');

        if (auth()->check()) {
            $statuses->whereNotIn('statuses.user_id', auth()->user()?->mutedUsers()->select('muted_id'))
                ->whereNotIn('statuses.user_id', auth()->user()?->blockedUsers()->select('blocked_id'))
                ->whereNotIn('statuses.user_id', auth()->user()?->blockedByUsers()->select('user_id'));
        }

        return [
            'event' => $event,
            'statuses' => $statuses,
        ];
    }

    public static function getFutureCheckins(): Paginator
    {
        return auth()->user()->statuses()
            ->with([
                'user', 'createdByUser', 'checkin.originStopover.station', 'checkin.destinationStopover.station',
                'checkin.trip', 'event', 'tags',
            ])
            ->orderByDesc('created_at')
            ->whereHas('checkin', function ($query) {
                $query->where('departure', '>=', date('Y-m-d H:i:s', strtotime('+20min')));
            })
            ->simplePaginate(15);
    }

    public static function createStatus(
        User|Authenticatable $user,
        Business $business,
        StatusVisibility $visibility,
        ?string $body = null,
        ?Event $event = null,
        ?User $createdByUser = null
    ): Status {
        if ($event !== null && !today()->isBetween($event->checkin_start, $event->checkin_end)) {
            Log::info('Event checkin was prevented because the event is not active anymore', [
                'event' => $event->only(['id', 'name', 'checkin_start', 'checkin_end']),
                'user' => $user->only(['id', 'username']),
            ]);
            $event = null;
        }

        return Status::create([
            'user_id' => $user->id,
            'created_by_user_id' => $createdByUser?->id,
            'body' => $body,
            'business' => $business,
            'visibility' => $visibility,
            'event_id' => $event?->id,
            'client_id' => APIController::getCurrentOAuthClient()?->id,
        ]);
    }

    /**
     * @throws RateLimitExceededException
     *                                    The rate limiter only hits if the users don't follow each other.
     */
    public static function likeRateLimiter(User $user, User $user2): void
    {

        $followEachOther = Follow::where('follows.user_id', $user->id)
            ->where('follows.follow_id', $user2->id)
            ->join('follows as f2', function ($join) use ($user2) {
                $join->on('follows.user_id', '=', 'f2.follow_id')
                    ->where('f2.user_id', $user2->id);
            })
            ->count() > 0;

        if ($followEachOther) {
            return;
        }

        $rateLimiterKey = "create-like:{$user->id}";
        if (RateLimiter::tooManyAttempts($rateLimiterKey, config('rate_limits.status_like.max_attempts'))) {
            throw new RateLimitExceededException(
                limit: config('rate_limits.status_like.max_attempts'),
                reset: RateLimiter::availableIn($rateLimiterKey),
            );
        }
        RateLimiter::hit($rateLimiterKey, 60 * config('rate_limits.status_like.decay_minutes'));
    }
}
