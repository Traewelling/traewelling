<?php

namespace App\Http\Controllers;

use App\Enum\Business;
use App\Enum\StatusVisibility;
use App\Events\StatusDeleteEvent;
use App\Events\StatusUpdateEvent;
use App\Exceptions\PermissionException;
use App\Exceptions\StatusAlreadyLikedException;
use App\Http\Controllers\Backend\GeoController;
use App\Models\Event;
use App\Models\Like;
use App\Models\Status;
use App\Models\User;
use App\Notifications\StatusLiked;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Gate;
use InvalidArgumentException;
use stdClass;
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
                                'event', 'likes', 'user.blockedByUsers', 'user.blockedUsers', 'trainCheckin',
                                'trainCheckin.originStation', 'trainCheckin.destinationStation',
                                'trainCheckin.HafasTrip.stopovers.trainStation',
                            ])
                     ->firstOrFail();
    }

    /**
     * This Method returns the current active status(es) for all users or a specific user.
     *
     * @return array|stdClass|null
     * @api v1
     * @frontend
     */
    public static function getActiveStatuses(bool $getPolylines = false): array|stdClass|null {
        $statuses = Status::with([
                                     'event', 'likes', 'user.blockedByUsers', 'user.blockedUsers', 'user.followers',
                                     'trainCheckin.originStation', 'trainCheckin.destinationStation',
                                     'trainCheckin.HafasTrip.stopovers.trainStation',
                                     'trainCheckin.HafasTrip.polyline',
                                 ])
                          ->whereHas('trainCheckin', function($query) {
                              $query->where('departure', '<', date('Y-m-d H:i:s'))
                                    ->where('arrival', '>', date('Y-m-d H:i:s'));
                          })
                          ->get()
                          ->filter(function(Status $status) {
                              return Gate::allows('view', $status) && !$status->user->shadow_banned && $status->visibility !== StatusVisibility::UNLISTED;
                          })
                          ->sortByDesc(function(Status $status) {
                              return $status->trainCheckin->departure;
                          })->values();

        if ($statuses === null) {
            return null;
        }
        $polylines = [];
        if ($getPolylines) {
            $polylines = $statuses->map(function($status) {
                return GeoController::getMapLinesForCheckin($status->trainCheckin);
            });
        }


        return ['statuses' => $statuses, 'polylines' => $polylines];
    }

    public static function getLivePositions() {
        $statuses = self::getActiveStatuses(true)['statuses'];

        $result = [];
        foreach ($statuses as $status) {
            $result[] = self::calculateLivePosition($status->trainCheckin->HafasTrip->stopovers, $status);
        }
        return $result;
    }

    private static function calculateLivePosition($stopovers, $status) {
        foreach ($stopovers as $key => $stopover) {
            if ($stopover->departure->isFuture()) {
                if ($stopover->arrival->isPast()) {
                    $newStopovers =  [$stopover];
                    break;
                }
                $newStopovers = [
                    $stopovers[$key - 1],
                    $stopover
                ];
                break;
            }
        }

        if (count($newStopovers) === 1) {
            return ['point' => self::createPoint([$newStopovers[0]->trainStation->longitude, $newStopovers[0]->trainStation->latitude])];
        }

        $now = Carbon::now()->timestamp;
        $percentage = ($now - $newStopovers[0]->departure->timestamp) / ($newStopovers[1]->arrival->timestamp - $newStopovers[0]->departure->timestamp);
        $polylines = GeoController::getPolylineBetween($status->trainCheckin->HafasTrip, $newStopovers[0], $newStopovers[1], false);
        $fullD = 0;
        $lastStopover = null;
        foreach ($polylines->features as $stopover) {
            if ($lastStopover === null) {
                $lastStopover = $stopover;
                continue;
            }
            $fullD += GeoController::calculateDistanceBetweenCoordinates(
                latitudeA:  $lastStopover->geometry->coordinates[0],
                longitudeA: $lastStopover->geometry->coordinates[1],
                latitudeB:  $stopover->geometry->coordinates[0],
                longitudeB: $stopover->geometry->coordinates[1]
            );
            $lastStopover = $stopover;
        }

        $meters = $fullD * $percentage;
        $recentPoint = null;
        $distance = 0;
        foreach ($polylines->features as $key => $point) {
            if (
                $recentPoint !== null
                && isset($point->geometry->coordinates)
                && isset($recentPoint->geometry->coordinates)
            ) {
                $d = GeoController::calculateDistanceBetweenCoordinates(
                    $point->geometry->coordinates[1],
                    $point->geometry->coordinates[0],
                    $recentPoint->geometry->coordinates[1],
                    $recentPoint->geometry->coordinates[0]
                );
                $distance += $d;
                if ($distance >= $meters) {

                    break;
                }
            }


            $recentPoint = $point;
        }
        $lastDistance = $distance - $d;

        $lat = $recentPoint->geometry->coordinates[1] + $meters/$distance * ($point->geometry->coordinates[1] - $recentPoint->geometry->coordinates[1]);
        $lon = $recentPoint->geometry->coordinates[0] + $meters/$distance * ($point->geometry->coordinates[0] - $recentPoint->geometry->coordinates[0]);

        $pointS = [$lon, $lat];

        $polylines->features = array_slice($polylines->features, $key);
        array_unshift($polylines->features, self::createPoint($pointS));

        return ['polyline' => $polylines, 'point' => self::createPoint($pointS), 'nextStop' => $newStopovers[1]->arrival->timestamp];
    }

    private static function createPoint($point) {
        return     [
            "type" => "Feature",
            "properties" => [
                "marker-color" => "#FF0000",
                "marker-size" => "medium",
                "marker-symbol" => "circle"
            ],
            "geometry"=> [
                "type"=> "Point",
                "coordinates" => $point
            ]
        ];
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
                                     'user.blockedUsers', 'trainCheckin.originStation',
                                     'trainCheckin.destinationStation', 'trainCheckin.HafasTrip.stopovers', 'event', 'likes',
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
                                'user', 'trainCheckin.originStation', 'trainCheckin.destinationStation',
                                'trainCheckin.HafasTrip', 'event',
                            ])
                     ->orderByDesc('created_at')
                     ->whereHas('trainCheckin', function($query) {
                         $query->where('departure', '>=', date('Y-m-d H:i:s', strtotime("+20min")));
                     })
                     ->simplePaginate(15);
    }

    public static function createStatus(
        User             $user,
        Business         $business,
        StatusVisibility $visibility,
        string           $body = null,
        int              $eventId = null, //TODO: change to Event Object
        string           $type = "hafas"
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
                                  'event_id'   => $event?->id
                              ]);
    }
}
