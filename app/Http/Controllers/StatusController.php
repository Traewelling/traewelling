<?php

namespace App\Http\Controllers;

use App\Exceptions\StatusAlreadyLikedException;
use App\Models\Event;
use App\Models\Like;
use App\Models\Status;
use App\Models\User;
use App\Notifications\StatusLiked;
use Barryvdh\DomPDF\Facade as PDF;
use Carbon\Carbon;
use DateInterval;
use DateTime;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class StatusController extends Controller
{
    /**
     * @api v1
     * @frontend
     * @param int $statusId
     * @return Status
     * @throws HttpException
     * @throws ModelNotFoundException
     */
    public static function getStatus(int $statusId): Status {
        $status = Status::where('id', $statusId)->with('user',
                                                       'trainCheckin',
                                                       'trainCheckin.Origin',
                                                       'trainCheckin.Destination',
                                                       'trainCheckin.HafasTrip',
                                                       'event')->withCount('likes')->firstOrFail();
        if (!$status->user->userInvisibleToMe) {
            return $status;
        }

        abort(403, "Status invisible to you.");
    }

    /**
     * This Method returns the current active status(es) for all users or a specific user.
     *
     * @api v1
     * @frontend
     * @param null $userId UserId to get the current active status for a user. Defaults to null.
     * @param bool $array This parameter is a temporary solution until the frontend is no more dependend on blade.
     * @return Status|array|Builder|Model|object|null
     */
    public static function getActiveStatuses($userId = null, bool $array = true) {
        if ($userId === null) {
            $statuses = Status::with([
                                         'likes',
                                         'user',
                                         'trainCheckin.Origin',
                                         'trainCheckin.Destination',
                                         'trainCheckin.HafasTrip.getPolyLine',
                                         'trainCheckin.HafasTrip.stopoversNEW.trainStation',
                                         'event'
                                     ])
                              ->whereHas('trainCheckin', function($query) {
                                  $query->where('departure', '<', date('Y-m-d H:i:s'))
                                        ->where('arrival', '>', date('Y-m-d H:i:s'));
                              })
                              ->get()
                              ->filter(function($status) {
                                  return !$status->user->userInvisibleToMe;
                              })
                              ->sortByDesc(function($status) {
                                  return $status->trainCheckin->departure;
                              })->values();
        } else {
            $status = Status::with([
                                       'user',
                                       'trainCheckin.Origin',
                                       'trainCheckin.Destination',
                                       'trainCheckin.HafasTrip.getPolyLine',
                                       'event'
                                   ])
                            ->whereHas('trainCheckin', function($query) {
                                $query->where('departure', '<', date('Y-m-d H:i:s'))
                                      ->where('arrival', '>', date('Y-m-d H:i:s'));
                            })
                            ->where('user_id', $userId)
                            ->first();
            if ($status?->user?->userInvisibleToMe) {
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
        if ($array == true) {
            return ['statuses' => $statuses->toArray(), 'polylines' => $polylines];
        }

        return ['statuses' => $statuses, 'polylines' => $polylines];
    }

    public static function getDashboard($user): Paginator {
        $userIds        = $user->follows->pluck('id');
        $userIds[]      = $user->id;
        $followingIDs   = $user->follows->pluck('id');
        $followingIDs[] = $user->id;
        return Status::with([
                                'event', 'likes', 'user', 'trainCheckin',
                                'trainCheckin.Origin', 'trainCheckin.Destination',
                                'trainCheckin.HafasTrip.stopoversNEW'
                            ])
                     ->join('train_checkins', 'train_checkins.status_id', '=', 'statuses.id')
                     ->select('statuses.*')
                     ->orderBy('train_checkins.departure', 'desc')
                     ->whereIn('user_id', $followingIDs)
                     ->withCount('likes')
                     ->latest()
                     ->simplePaginate(15);
    }


    public static function getGlobalDashboard(): Paginator {
        $follows = Auth::user()->follows()->select('follow_id');
        return Status::with([
                                'event', 'likes', 'user', 'trainCheckin',
                                'trainCheckin.Origin', 'trainCheckin.Destination',
                                'trainCheckin.HafasTrip.stopoversNEW'
                            ])
                     ->whereHas('user', function($query) use ($follows) {
                         return $query->where('private_profile', false)
                                      ->orWhere('user_id', Auth::user()->id)
                                      ->orWhereIn('user_id', $follows);
                     })
                     ->join('train_checkins', 'train_checkins.status_id', '=', 'statuses.id')
                     ->select('statuses.*')
                     ->orderBy('train_checkins.departure', 'desc')
                     ->withCount('likes')
                     ->simplePaginate(15);
    }

    public static function DeleteStatus($user, $statusId): ?bool {
        $status = Status::find($statusId);

        if ($status === null) {
            return null;
        }
        $trainCheckin = $status->trainCheckin()->first();

        if ($user != $status->user) {
            return false;
        }
        $user->train_distance -= $trainCheckin->distance;
        $user->train_duration -= $trainCheckin->duration;

        $user->update();
        $status->delete();
        return true;
    }

    public static function EditStatus($user, $statusId, $body, $businessCheck): bool|string|null {
        $status = Status::find($statusId);
        if ($status === null) {
            return null;
        }
        if ($user != $status->user) {
            return false;
        }

        $status->body     = $body;
        $status->business = $businessCheck;
        $status->update();
        return $status->body;
    }

    /**
     * Create a Statuslike for a given User
     * @param User $user
     * @param Status $status
     * @return Like
     * @throws StatusAlreadyLikedException
     */
    public static function createLike(User $user, Status $status): Like {

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

    public static function DestroyLike($user, $statusId): bool {
        $like = $user->likes()->where('status_id', $statusId)->first();
        if ($like) {
            $like->delete();
            return true;
        }
        return false;
    }

    public static function getLikes($statusId) {
        return Status::findOrFail($statusId)->likes()->with('user')->simplePaginate(15);
    }

    public static function ExportStatuses($startDate,
                                          $endDate,
                                          $fileType,
                                          $privateTrips = true,
                                          $businessTrips = true) {
        if (!$privateTrips && !$businessTrips) {
            abort(400, __('controller.status.export-neither-business'));
        }
        $endInclLastOfMonth = (new DateTime($endDate))->add(new DateInterval("P1D"))->format("Y-m-d");

        $user          = Auth::user();
        $trainCheckins = Status::with('user',
                                      'trainCheckin',
                                      'trainCheckin.Origin',
                                      'trainCheckin.Destination',
                                      'trainCheckin.hafastrip')
                               ->where('user_id', $user->id)
                               ->whereHas('trainCheckin', function($query) use ($startDate, $endInclLastOfMonth) {
                                   $query->whereBetween('arrival', [$startDate, $endInclLastOfMonth]);
                                   $query->orwhereBetween('departure', [$startDate, $endInclLastOfMonth]);
                               })
                               ->get()->sortBy('trainCheckin.departure');
        $export        = [];

        foreach ($trainCheckins as $t) {
            $interval = (new \DateTime($t->trainCheckin->departure))->diff(new \DateTime($t->trainCheckin->arrival));
            $export   = array_merge($export, [[
                                                  (string) $t->id,
                                                  $t->trainCheckin->hafastrip->category,
                                                  $t->trainCheckin->hafastrip->linename,
                                                  $t->trainCheckin->Origin->name,
                                                  $t->trainCheckin->Origin->latitude . ', ' . $t->trainCheckin->Origin->longitude,
                                                  $t->trainCheckin->departure,
                                                  $t->trainCheckin->Destination->name,
                                                  $t->trainCheckin->Destination->latitude . ', ' . $t->trainCheckin->Destination->longitude,
                                                  $t->trainCheckin->arrival,
                                                  $interval->h . ":" . sprintf('%02d', $interval->i),
                                                  $t->trainCheckin->distance,
                                                  $t->trainCheckin->points,
                                                  (string) $t->body,
                                                  '',
                                                  $t->business,
                                              ]]);
        }
        if ($fileType == 'pdf') {
            $pdf = PDF::loadView('pdf.export-template',
                                 ['export'     => $export,
                                  'name'       => $user->name,
                                  'start_date' => $startDate,
                                  'end_date'   => $endDate])
                      ->setPaper('a4', 'landscape');
            return $pdf->download(sprintf(config('app.name', 'Träwelling') . '_export_%s_to_%s.pdf',
                                          $startDate,
                                          $endDate));
        }

        if ($fileType == 'csv') {
            $headers  = [
                'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0',
                'Content-type'        => 'text/csv',
                'Content-Disposition' => sprintf('attachment; filename="' .
                                                 config('app.name', 'Träwelling') .
                                                 '_export_%s_to_%s.csv"',
                                                 $startDate,
                                                 $endDate),
                'Expires'             => '0',
                'Pragma'              => 'public'
            ];
            $callback = function() use ($export) {
                $fileStream = fopen('php://output', 'w');
                fputcsv($fileStream, [
                    __('export.title.status-id'),
                    __('export.title.train-type'),
                    __('export.title.train-number'),
                    __('export.title.origin.location'),
                    __('export.title.origin.coordinates'),
                    __('export.title.origin.time'),
                    __('export.title.destination.location'),
                    __('export.title.destination.coordinates'),
                    __('export.title.destination.time'),
                    __('export.title.travel-time'),
                    __('export.title.kilometer'),
                    __('export.title.points'),
                    __('export.title.status'),
                    __('export.title.stopovers'),
                    __('export.title.type'),
                ], "\t");
                foreach ($export as $t) {
                    fputcsv($fileStream, $t, "\t");
                }
                fclose($fileStream);
            };
            return Response::stream($callback, 200, $headers);
        }

        // Else: $filetype == 'json', fallback
        $headers = [
            'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0',
            'Content-type'        => 'text/json',
            'Content-Disposition' => sprintf('attachment; filename="' .
                                             config('app.name', 'Träwelling') .
                                             '_export_%s_to_%s.json"',
                                             $startDate,
                                             $endDate),
            'Expires'             => '0',
            'Pragma'              => 'public'
        ];
        return Response::json($trainCheckins, 200, $headers);
    }

    public static function usageByDay(Carbon $date): int {
        return Status::where("created_at", ">=", $date->copy()->startOfDay())
                     ->where("created_at", "<=", $date->copy()->endOfDay())
                     ->count();
    }

    /**
     * @param string|null $slug
     * @param int|null $id
     * @return array
     */
    public static function getStatusesByEvent(?string $slug, ?int $id): array {
        if ($slug != null) {
            $event = Event::where('slug', $slug)->firstOrFail();
        }
        if ($id != null) {
            $event = Event::findOrFail($id);
        }


        $statusesResponse = $event->statuses()
                                  ->with('user')
                                  ->whereHas('user', function($query) {
                                      //ToDo Not a bug, but a feature
                                      // This is a hacky implementation to just __not__ show private profiles in events
                                      // b/c it's just... not worth it.
                                      return $query->where('private_profile', false);
                                  })
                                  ->simplePaginate(15);

        return ['event' => $event, 'statuses' => $statusesResponse];
    }
}
