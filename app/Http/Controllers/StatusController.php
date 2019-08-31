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
        $statuses = Status::whereIn('user_id', $userIds)->latest()->get();

        if ($statuses->isEmpty()) {
            return redirect()->route('globaldashboard');
        }

        return view('dashboard', ['statuses' => $statuses]);
    }

    public function getGlobalDashboard() {
        $statuses = Status::orderBy('created_at', 'desc')->latest()->get();

        return view('dashboard', ['statuses' => $statuses]);
    }

    public function CreateStatus(Request $request) {
        $this->validate($request, [
            'body' => 'max:280'
        ]);
        $status = new Status();
        $status->body = $request['body'];
        $message = 'There was an error.';
        if ($request->user()->statuses()->save($status)) {
            $message = 'Status successfully created!';
        }

        return redirect()->route('dashboard')->with(['message' => $message]);
    }

    public function DeleteStatus(Request $request) {
        $status = Status::find($request['statusId']);
        if (Auth::user() != $status->user) {
            return redirect()->back()->with('error', __('You \'re not permitted to do this'));
        }
        $status->delete();
        $status->trainCheckin()->delete();
        return response()->json(['message' => 'Status successfully deleted.'], 200);
    }

    public function EditStatus(Request $request) {
        $this->validate($request, [
            'body' => 'max:280'
        ]);
        $status = Status::find($request['statusId']);
        if (Auth::user() != $status->user) {
            return redirect()->back();
        }
        $status->body = $request['body'];
        $status->update();
        return response()->json(['new_body' => $status->body], 200);
    }

    public function createLike(Request $request) {
        $statusID = $request->statusId;
        $status = Status::find($statusID);

        if (!$status) {
            return 'no Status';
        }
        $user = Auth::user();
        $like = $user->likes()->where('status_id', $statusID)->first();

        if ($like) {
            return response('Like already exists', 409);
        }

        $like = new Like();
        $like->user_id = $user->id;
        $like->status_id = $status->id;
        $like->save();
        return response('Like created', 201);
    }

    public function destroyLike(Request $request) {
        $statusID = $request->statusId;
        $user = Auth::user();
        $like = $user->likes()->where('status_id', $statusID)->first();

        if ($like) {
            $like->delete();
            return response('Like deleted', 200);
        }

        return response('Like not found', 404);
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
        return vsprintf("\"%s\"\t\"%s\"\t\"%s\"\t\"%s\"\t\"%s\"\t\"%s\"\t\"%s\"\t\"%s\"\t\"%s\"\t\"%s\"\t\"%s\"\t\"%s\"\t\"%s\"\t\"%s\"\t\n", $array);
    }

    public function exportCSV(Request $request) {
        $begin = $request->input('begin');
        $end = $request->input('end');
        
        if(!$this->isValidDate($begin) || !$this->isValidDate($end)) {
            return redirect(route('export.landing'));
        }

        $end = (new \DateTime($end))->add(new \DateInterval("P1D"))->format("Y-m-d");

        $user = Auth::user();

        $trainCheckins = DB::table('train_checkins')
            ->join('statuses', 'statuses.id', '=', 'train_checkins.status_id')
            ->select('train_checkins.*', 'statuses.user_id')
            ->whereBetween('train_checkins.departure', [$begin, $end])
            ->orWhereBetween('train_checkins.arrival', [$begin, $end])
            ->take(100)->get();

        echo $this->writeLine(
            ["Status-ID",           "Zugart",
            "Zugnummer",            "Abfahrtsort",
            "Abfahrtskoordinaten",  "Abfahrtszeit",
            "Ankunftsort",          "Ankunftskoordinaten",
            "Ankunftszeit",         "Reisezeit",
            "Kilometer",            "Punkte",
            "Status",               "Zwischenhalte"]
        );

        foreach ($trainCheckins as $t) {
            if ($t->user_id !== $user->id) {
                continue;
            }

            $hafas = HafasTrip::where('trip_id', $t->trip_id)->first();
            $origin = TrainStations::where('ibnr', $t->origin)->first();
            $destination = TrainStations::where('ibnr', $t->destination)->first();

            $interval = (new \DateTime($t->departure))->diff(new \DateTime($t->arrival));

            $checkin = [$t->status_id, $hafas->category,
                $hafas->linename, $origin->name,
                $origin->latitude . ", " . $origin->longtitude, $t->departure,
                $destination->name, $destination->latitude . ", " . $destination->longtitude,
                $t->arrival, $interval->h . ":" . $interval->i,
                $t->distance, $t->points,
                Status::find($t->status_id)->first()->body, ""
            ];
            echo $this->writeLine($checkin);
            
        }
    }
}
