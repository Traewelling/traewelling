<?php

namespace App\Http\Controllers\Frontend\Admin;

use App\Dto\Coordinate;
use App\Exceptions\Wikidata\FetchException;
use App\Http\Controllers\Controller;
use App\Models\Station;
use App\Objects\LineSegment;
use App\Services\Wikidata\WikidataImportService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
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

    /**
     * !!!! Experimental Backend Function !!!!
     * Fetches the Wikidata information for a station.
     * Try to find matching Wikidata entity for the station and fetch it.
     * Needs to be cleaned up and refactored, if it should be used consistently.
     * Little testing if it works as expected.
     */
    public function fetchWikidata(int $id): JsonResponse {
        $station = Station::findOrFail($id);
        $this->authorize('update', $station);

        try {
            WikidataImportService::searchStation($station);
            return response()->json(['success' => 'Wikidata information fetched successfully']);
        } catch (FetchException $exception) {
            return response()->json(['error' => $exception->getMessage()], 422);
        }
    }

    /**
     * @param Request $request
     *
     * @return RedirectResponse
     * @throws AuthorizationException
     * @todo Make this an API endpoint when it is accessible for users too
     */
    public function importWikidata(Request $request): RedirectResponse {
        $this->authorize('create', Station::class);
        $validated = $request->validate([
                                            'qId' => ['required', 'string', 'regex:/^Q\d+$/'],
                                        ]);
        try {
            $station = WikidataImportService::importStation($validated['qId']);
            return redirect()->route('admin.station', ['id' => $station->id])->with('alert-success', 'Station imported successfully');
        } catch (\Exception $exception) {
            Log::error('Error while importing wikidata station (manually): ' . $exception->getMessage());
            return redirect()->back()->with('alert-danger', 'Error while importing station: ' . $exception->getMessage());
        }
    }
}
