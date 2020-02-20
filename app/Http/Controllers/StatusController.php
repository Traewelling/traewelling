<?php

namespace App\Http\Controllers;

use App\HafasTrip;
use App\Like;
use App\Status;
use App\TrainCheckin;
use App\TrainStations;
use App\Notifications\StatusLiked;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use Barryvdh\DomPDF\Facade as PDF;

class StatusController extends Controller
{
    public static function getStatus($id) {
        return Status::where('id', $id)->firstOrFail(); //I'm not sure if that's the correct way to do. Will need to revisit this during API-Development.
    }

    public static function getActiveStatuses() {
        $statuses = Status::with('trainCheckin')
            ->whereHas('trainCheckin', function ($query) {
                $query->where('departure', '<', date('Y-m-d H:i:s'))->where('arrival', '>', date('Y-m-d H:i:s'));
            })
            ->get()
            ->sortByDesc(function ($status, $key) {
                return $status->trainCheckin->departure;
            });
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
        $statuses = Status::whereIn('user_id', $userIds)->latest()->simplePaginate(15);

        return $statuses;
    }

    public static function getGlobalDashboard() {
        return Status::orderBy('created_at', 'desc')->latest()->simplePaginate(15);
    }

    public static function DeleteStatus($user, $statusId) {
        $status = Status::find($statusId);
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
        $status->user->notify(new StatusLiked($user, $status));
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

    public static function ExportStatuses($start_date, $end_date, $filetype, $private_trips=true, $business_trips=true) {
        if(!$private_trips && !$business_trips) {
            abort(400, __('controller.status.export-neither-business'));
        }
        $endInclLastOfMonth = (new \DateTime($end_date))->add(new \DateInterval("P1D"))->format("Y-m-d");

        $user = Auth::user();
        $trainCheckins = Status::with('user', 'trainCheckin', 'trainCheckin.Origin', 'trainCheckin.Destination', 'trainCheckin.hafastrip')->where('user_id', $user->id)
            ->whereHas('trainCheckin', function ($query) use ($start_date, $endInclLastOfMonth){
                $query->whereBetween('arrival', [$start_date, $endInclLastOfMonth]);
                $query->orwhereBetween('departure', [$start_date, $endInclLastOfMonth]);
            })
            ->get()->sortBy('trainCheckin.departure');
        $export = array();
        foreach ($trainCheckins as $t) {
            $interval = (new \DateTime($t->trainCheckin->departure))->diff(new \DateTime($t->trainCheckin->arrival));
            $export = array_merge($export, array([
                (String)$t->id,
                $t->trainCheckin->hafastrip->category,
                $t->trainCheckin->hafastrip->linename,
                $t->trainCheckin->Origin->name,
                $t->trainCheckin->Origin->latitude. ', ' .$t->trainCheckin->Origin->longitude,
                $t->trainCheckin->departure,
                $t->trainCheckin->Destination->name,
                $t->trainCheckin->Destination->latitude. ', ' .$t->trainCheckin->Destination->longitude,
                $t->trainCheckin->arrival,
                $interval->h . ":" . sprintf('%02d', $interval->i),
                $t->trainCheckin->distance,
                $t->trainCheckin->points,
                (String)$t->trainCheckin->body,
                ''
            ]));
        }
        if ($filetype == 'pdf') {
            $pdf = PDF::loadView('pdf.export-template', ['export' => $export, 'name' => $user->name, 'start_date' => $start_date, 'end_date' => $end_date])->setPaper('a4', 'landscape');
            return $pdf->download(sprintf(config('app.name', 'Träwelling') . '_export_%s_to_%s.pdf', $start_date, $end_date));
        }

        if ($filetype == 'csv') {
            $headers = [
                'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0',
                'Content-type'        => 'text/csv',
                'Content-Disposition' => sprintf('attachment; filename="' . config('app.name', 'Träwelling') . '_export_%s_to_%s.csv"',$start_date, $end_date),
                'Expires'             => '0',
                'Pragma'              => 'public'
            ];
            $callback = function() use ($export)
            {
                $FH = fopen('php://output', 'w');
                fputcsv($FH, ['Status-ID',
                              'Zugart',
                              'Zugnummer',
                              'Abfahrtsort',
                              'Abfahrtskoordinaten',
                              'Abfahrtszeit',
                              'Ankunftsort',
                              'Ankunftskoordinaten',
                              'Ankunftszeit',
                              'Reisezeit',
                              'Kilometer',
                              'Punkte',
                              'Status',
                              'Zwischenhalte'
                ], "\t");
                foreach ($export as $t) {
                    fputcsv($FH, $t, "\t");
                }
                fclose($FH);
            };
            return Response::stream($callback, 200, $headers);
        }

        // Else: $filetype == 'json', fallback
        $headers = [
            'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0',
            'Content-type'        => 'text/json',
            'Content-Disposition' => sprintf('attachment; filename="' . config('app.name', 'Träwelling') . '_export_%s_to_%s.json"', $start_date, $end_date),
            'Expires'             => '0',
            'Pragma'              => 'public'
        ];
        return Response::json($trainCheckins, 200, $headers);
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
