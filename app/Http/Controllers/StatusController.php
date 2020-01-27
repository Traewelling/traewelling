<?php

namespace App\Http\Controllers;

use App\HafasTrip;
use App\Like;
use App\Status;
use App\TrainCheckin;
use App\TrainStations;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;

class StatusController extends Controller
{
    public static function getStatus($id) {
        return Status::where('id', $id)->with('user', 'trainCheckin', 'trainCheckin.Origin', 'trainCheckin.Destination', 'trainCheckin.HafasTrip')->firstOrFail(); //I'm not sure if that's the correct way to do. Will need to revisit this during API-Development.
    }

    public static function getActiveStatuses($userid=null) {
        if ($userid === null) {
            $statuses = Status::with('user', 'trainCheckin', 'trainCheckin.Origin', 'trainCheckin.Destination', 'trainCheckin.HafasTrip')
                ->whereHas('trainCheckin', function ($query) {
                    $query->where('departure', '<', date('Y-m-d H:i:s'))->where('arrival', '>', date('Y-m-d H:i:s'));
                })
                ->get()
                ->sortByDesc(function ($status, $key) {
                    return $status->trainCheckin->departure;
                });
        } else {
            $statuses = Status::with('user', 'trainCheckin', 'trainCheckin.Origin', 'trainCheckin.Destination', 'trainCheckin.HafasTrip')
                ->whereHas('trainCheckin', function ($query) {
                    $query->where('departure', '<', date('Y-m-d H:i:s'))->where('arrival', '>', date('Y-m-d H:i:s'));
                })
                ->where('user_id', $userid)
                ->first();
            return $statuses;
        }
        if ($statuses === null) {
            return null;
        }
        $polylines = $statuses->map(function($status) {
            return $status->trainCheckin->getMapLines();
        });
        return ['statuses' => $statuses, 'polylines' => $polylines];
    }

    public static function getStatusesByEvent(int $eventId) {
        return Status::with('trainCheckin')
            ->where('event_id', '=', $eventId)
            ->orderBy('created_at', 'desc')
            ->latest()
            ->simplePaginate(15);
    }

    public static function getDashboard($user) {
        $userIds = $user->follows()->pluck('follow_id');
        $userIds[] = $user->id;
        $statuses = Status::whereIn('user_id', $userIds)->with('user', 'trainCheckin', 'trainCheckin.Origin', 'trainCheckin.Destination', 'trainCheckin.HafasTrip')->latest()->simplePaginate(15);

        return $statuses;
    }

    public static function getGlobalDashboard() {
        return Status::orderBy('created_at', 'desc')->with('user', 'trainCheckin', 'trainCheckin.Origin', 'trainCheckin.Destination', 'trainCheckin.HafasTrip')->latest()->simplePaginate(15);
    }

    public static function DeleteStatus($user, $statusId) {
        $status = Status::find($statusId);
        if ($status === null) {
            return null;
        }
        $trainCheckin = $status->trainCheckin()->first();
        if ($user != $status->user) {
            return false;
        }
        $user->train_distance -= $trainCheckin->distance;
        $user->train_duration -= (strtotime($trainCheckin->arrival) - strtotime($trainCheckin->departure)) / 60;

        //Don't subtract points, if status outside of current point calculation
        if (strtotime($trainCheckin->departure) >= date(strtotime('last thursday 3:14am'))) {
            $user->points -= $trainCheckin->points;
        }
        $user->update();
        $status->delete();
        $trainCheckin->delete();
        return true;
    }

    public static function EditStatus($user, $statusId, $body, $businessCheck) {
        $status = Status::find($statusId);
        if ($status === null) {
            return null;
        }
        if ($user != $status->user) {
            return false;
        }
        $status->body = $body;
        $status->business = $businessCheck >= 1 ? 1 : 0;
        $status->update();
        return $status->body;
    }

    public static function CreateLike($user, $statusId) {
        $status = Status::find($statusId);
        if (!$status) {
            return null;
        }
        $like = $user->likes()->where('status_id', $statusId)->first();
        if ($like) {
            return false;
        }

        $like = new Like();
        $like->user_id = $user->id;
        $like->status_id = $status->id;
        $like->save();
        return true;
    }

    public static function DestroyLike($user, $statusId) {
        $like = $user->likes()->where('status_id', $statusId)->first();
        if ($like) {
            $like->delete();
            return true;
        }
        return false;
    }
    //ToDo
    //this needs to be rewritten. Maybe use a library?
    public function exportCSV(Request $request) {
        $begin = $request->input('begin');
        $end = $request->input('end');
        if(!$this->isValidDate($begin) || !$this->isValidDate($end)) {
            return redirect(route('export.landing'))->with(['message' => __('controller.status.export-invalid-dates')]);
        }

        $private = $request->input('private-trips', false) == 'true';
        $business = $request->input('business-trips', false) == 'true';
        if(!$private && !$business) {
            return redirect(route('export.landing'))->with(['message' => __('controller.status.export-neither-business')]);
        }

        $endInclLastOfMonth = (new \DateTime($end))->add(new \DateInterval("P1D"))->format("Y-m-d");

        $user = Auth::user();

        $trainCheckins = TrainCheckin::with('Status')->whereHas('Status', function ($query) use ($user) {
            $query->where('user_id', $user->id);
        })->whereBetween('arrival', [$begin, $endInclLastOfMonth])->orwhereBetween('departure', [$begin, $endInclLastOfMonth])->get();

        $return = $this->writeLine(
            ["Status-ID",           "Zugart",
            "Zugnummer",            "Abfahrtsort",
            "Abfahrtskoordinaten",  "Abfahrtszeit",
            "Ankunftsort",          "Ankunftskoordinaten",
            "Ankunftszeit",         "Reisezeit",
            "Kilometer",            "Punkte",
            "Status",              // "Business-Reise",
            "Zwischenhalte"]
        );

        foreach ($trainCheckins as $t) {
            if ($t->status->user_id != $user->id) {
                continue;
            }
            // if (!(
            //     ($business && $t->status->business)
            //     ||
            //     ($private && !$t->status->business))
            //     ) {
            //     continue;
            // }

            $hafas = HafasTrip::where('trip_id', $t->trip_id)->first();
            $origin = TrainStations::where('ibnr', $t->origin)->first();
            $destination = TrainStations::where('ibnr', $t->destination)->first();

            $interval = (new \DateTime($t->departure))->diff(new \DateTime($t->arrival));

            $checkin = [$t->status_id, $hafas->category,
                $hafas->linename, $origin->name,
                $origin->latitude . ", " . $origin->longitude, $t->departure,
                $destination->name, $destination->latitude . ", " . $destination->longitude,
                $t->arrival, $interval->h . ":" . $interval->i,
                $t->distance, $t->points,
                $t->status->body, // $t->status->business,
                ""
            ];
            $return .= $this->writeLine($checkin);
        }

        $return_8859_1 = iconv("UTF-8", "ISO-8859-1", $return);

        return Response::make($return_8859_1, 200, [
        'Content-type' => 'text/csv',
        'Content-Disposition' => sprintf('attachment; filename="traewelling_export_%s_to_%s.csv"', $begin, $end),
        'Content-Length' => strlen($return_8859_1)
        ]);
    }

    public function writeLine($array): String {
        return vsprintf("\"%s\"\t\"%s\"\t\"%s\"\t\"%s\"\t\"%s\"\t\"%s\"\t\"%s\"\t\"%s\"\t\"%s\"\t\"%s\"\t\"%s\"\t\"%s\"\t\"%s\"\t\"%s\"\t\n", $array);
    }

    public static function usageByDay(Carbon $date) {
        $q = DB::table('statuses')
            ->select(DB::raw('count(*) as occurs'))
            ->where("created_at", ">=", $date->copy()->startOfDay())
            ->where("created_at", "<=", $date->copy()->endOfDay())
            ->first();
        return $q;
    }
}
