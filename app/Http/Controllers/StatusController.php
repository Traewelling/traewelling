<?php

namespace App\Http\Controllers;

use App\Like;
use App\Status;
use App\HafasTrip;
use App\TrainCheckin;
use App\TrainStations;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;

class StatusController extends Controller
{
    public function getStatus($id) {
        $status = Status::where('id', $id)->first();

        return view('status', compact('status'));
    }

    public function getDashboard() {
        $user = Auth::user();
        $userIds = $user->follows()->pluck('follow_id');
        $userIds[] = $user->id;
        $statuses = Status::whereIn('user_id', $userIds)->latest()->simplePaginate(15);

        if (!$user->hasVerifiedEmail() && $user->email != null) {
            \Session::flash('message',
                __('controller.status.email-not-verified',
                    ['url' => route('verification.resend')]
                )
            );
        }

        if ($statuses->isEmpty()) {
            return redirect()->route('globaldashboard');
        }

        return view('dashboard', ['statuses' => $statuses]);
    }

    public function getGlobalDashboard() {
        $statuses = Status::orderBy('created_at', 'desc')->latest()->simplePaginate(15);

        return view('dashboard', ['statuses' => $statuses]);
    }

    public function CreateStatus(Request $request) {
        $this->validate($request, [
            'body' => 'max:280',
            'business_check' => 'max:2',
        ]);
        $status = new Status();
        $status->body = $request['body'];
        $status->business = $request['business_check'] == "on" ? 1 : 0;
        $message = 'There was an error.';
        if ($request->user()->statuses()->save($status)) {
            $message = __('controller.status.create-success');
        }

        return redirect()->route('dashboard')->with(['message' => $message]);
    }

    public function DeleteStatus(Request $request) {
        $status = Status::find($request['statusId']);
        $trainCheckin = $status->trainCheckin()->first();
        $user = Auth::user();
        if ($user != $status->user) {
            return redirect()->back()->with('error', __('controller.status.not-permitted'));
        }

        $user->train_distance -= $trainCheckin->distance;
        $user->train_duration -= (strtotime($trainCheckin->arrival) - strtotime($trainCheckin->departure)) / 60;


        if (strtotime($trainCheckin->departure) >= date(strtotime('last thursday 3:14am'))) {
            $user->points -= $trainCheckin->points;
        }

        $user->update();
        $status->delete();
        $trainCheckin->delete();
        return response()->json(['message' => __('controller.status.delete-ok')], 200);
    }

    public function EditStatus(Request $request) {
        $this->validate($request, [
            'body' => 'max:280',
            'businessCheck' => 'max:1',
        ]);
        $status = Status::find($request['statusId']);
        if (Auth::user() != $status->user) {
            return redirect()->back();
        }
        $status->body = $request['body'];
        $status->business = $request['businessCheck'] >= 1 ? 1 : 0;
        $status->update();
        return response()->json(['new_body' => $status->body], 200);
    }

    public function createLike(Request $request) {
        $statusID = $request->statusId;

        $status = Status::find($statusID);

        if (!$status) {
            return response(__('controller.status.status-not-found'), 404);
        }
        $user = Auth::user();
        $like = $user->likes()->where('status_id', $statusID)->first();

        if ($like) {
            return response(__('controller.status.like-already'), 409);
        }

        $like = new Like();
        $like->user_id = $user->id;
        $like->status_id = $status->id;
        $like->save();
        return response(__('controller.status.like-ok'), 201);
    }

    public function destroyLike(Request $request) {
        $statusID = $request->statusId;
        $user = Auth::user();
        $like = $user->likes()->where('status_id', $statusID)->first();

        if ($like) {
            $like->delete();
            return response(__('controller.status.like-deleted'), 200);
        }

        return response(__('controller.status.like-not-found'), 404);
    }

    public function exportLanding() {
        return view('export')->with([
            'begin_of_month' => (new \DateTime("first day of this month"))->format("Y-m-d"),
            'end_of_month' => (new \DateTime("last day of this month"))->format("Y-m-d")
        ]);
    }

    private function isValidDate($date): Bool {
        try {
            $d = new \DateTime($date);
        } catch (\Exception $e) {
            echo $e->getMessage();
            return false;
        }
        return $date === $d->format("Y-m-d");
    }

    private function writeLine($array): String {
        return vsprintf("\"%s\"\t\"%s\"\t\"%s\"\t\"%s\"\t\"%s\"\t\"%s\"\t\"%s\"\t\"%s\"\t\"%s\"\t\"%s\"\t\"%s\"\t\"%s\"\t\"%s\"\t\"%s\"\t\"%s\"\t\n", $array);
    }

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
            "Status",               "Business-Reise",
            "Zwischenhalte"]
        );

        foreach ($trainCheckins as $t) {
            if ($t->status->user_id != $user->id) {
                continue;
            }
            if (!(
                ($business && $t->status->business)
                ||
                ($private && !$t->status->business))
                ) {
                continue;
            }

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
                $t->status->body, $t->status->business,
                ""
            ];
            $return .= $this->writeLine($checkin);
        }

        return Response::make($return, 200, [
        'Content-type' => 'text/csv',
        'Content-Disposition' => sprintf('attachment; filename="traewelling_export_%s_to_%s.csv"', $begin, $end),
        'Content-Length' => strlen($return)
        ]);
    }

    public function getActiveStatuses() {
        $statuses = Status::with('trainCheckin')->whereHas('trainCheckin', function ($query) {
            $query->where('departure', '<', date('Y-m-d H:i:s'))->where('arrival', '>', date('Y-m-d H:i:s'));
        })->get()->sortByDesc(function ($status, $key) {
            return $status->trainCheckin->departure;
        });

        $polylines = $statuses->map(function($s) {
            return $s->trainCheckin->getMapLines();
        });

        return view('activejourneys', ['statuses' => $statuses, 'polylines' => $polylines]);
    }
}
