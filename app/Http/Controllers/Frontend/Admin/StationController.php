<?php

namespace App\Http\Controllers\Frontend\Admin;

use App\Dto\Coordinate;
use App\Http\Controllers\Controller;
use App\Models\Station;
use App\Objects\LineSegment;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StationController extends Controller
{

    public function renderList(Request $request): View {
        $this->authorize('viewAny', Station::class);
        $stations = Station::orderByDesc('created_at');
        if ($request->has('query')) {
            $stations->where('name', 'LIKE', '%' . strip_tags($request->get('query')) . '%')
                     ->orWhere('ibnr', 'LIKE', '%' . strip_tags($request->get('query')) . '%')
                     ->orWhere('rilIdentifier', 'LIKE', '%' . strip_tags($request->get('query')) . '%');
        }
        return view('admin.stations.list', [
            'stations' => $stations->paginate(20),
        ]);
    }

    public function renderStation(int $id): View {
        $this->authorize('viewAny', Station::class);

        $station = Station::findOrFail($id);

        if (isset($station->ifopt_a, $station->ifopt_b, $station->ifopt_c)) {
            $stationsWithSameIfopt = Station::where('ifopt_a', $station->ifopt_a)
                                            ->where('ifopt_b', $station->ifopt_b)
                                            ->where('ifopt_c', $station->ifopt_c)
                                            ->limit(100)
                                            ->get()
                                            ->reject(fn(Station $s) => $s->id === $station->id)
                                            ->map(function(Station $s) use ($station) {
                                                $stationCoordinate           = new Coordinate($s->latitude, $s->longitude);
                                                $sameStationCoordinate       = new Coordinate($station->latitude, $station->longitude);
                                                $lineSegment                 = new LineSegment($stationCoordinate, $sameStationCoordinate);
                                                $s->distanceToSimilarStation = $lineSegment->calculateDistance();
                                                return $s;
                                            });
        }

        return view('admin.stations.show', [
            'station'               => $station,
            'stationsWithSameIfopt' => $stationsWithSameIfopt ?? [],
        ]);
    }
}
