<?php

namespace App\Http\Controllers\Frontend\Admin;

use App\Dto\Coordinate;
use App\Exceptions\DataProviderException;
use App\Exceptions\Wikidata\FetchException;
use App\Http\Controllers\Controller;
use App\Http\Resources\StationResource;
use App\Models\Station;
use App\Objects\LineSegment;
use App\Services\StationService;
use App\Services\Wikidata\WikidataImportService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class StationController extends Controller
{
    public function index(Request $request): View|RedirectResponse
    {
        $this->authorize('viewAny', Station::class);
        $stations = Station::orderByDesc('created_at');
        if ($request->has('query')) {
            $query = strip_tags($request->get('query'));

            if (is_numeric($query)) {
                $stations->where('id', $query);
                if ($stations->exists()) {
                    return redirect()->route('admin.station', ['id' => $query]);
                }
            }

            $stations->where('name', 'LIKE', '%' . $query . '%')
                ->orWhereHas('stationIdentifiers', function ($q) use ($query) {
                    $q->where('identifier', 'LIKE', '%' . $query . '%');
                });

            if ($stations->count() === 1) {
                return redirect()->route('admin.station', ['id' => $stations->first()->id]);
            }
        }

        return view('admin.stations.index', [
            'stations' => $stations->paginate(20),
        ]);
    }

    public function show(int $id): View
    {
        $this->authorize('viewAny', Station::class);

        $station = Station::findOrFail($id);

        $ifopt = $station->getIdentifier(\App\StationIdentifierType::DE_DB_IFOPT);
        if ($ifopt) {
            // Extract first 3 parts of IFOPT (country:area:mode) to find similar stations
            $ifoptParts = explode(':', $ifopt);
            if (count($ifoptParts) >= 3) {
                $ifoptPrefix = $ifoptParts[0] . ':' . $ifoptParts[1] . ':' . $ifoptParts[2];

                $stationsWithSameIfopt = Station::whereHas('stationIdentifiers', function ($query) use ($ifoptPrefix) {
                    $query->where('type', \App\StationIdentifierType::DE_DB_IFOPT)
                        ->where('identifier', 'LIKE', $ifoptPrefix . '%');
                })
                    ->limit(100)
                    ->get()
                    ->reject(fn (Station $s) => $s->id === $station->id)
                    ->map(function (Station $s) use ($station) {
                        $stationCoordinate = new Coordinate($s->latitude, $s->longitude);
                        $sameStationCoordinate = new Coordinate($station->latitude, $station->longitude);
                        $lineSegment = new LineSegment($stationCoordinate, $sameStationCoordinate);
                        $s->distanceToSimilarStation = $lineSegment->calculateDistance();

                        return $s;
                    });
            }
        }

        return view('admin.stations.show', [
            'station' => $station,
            'stationsWithSameIfopt' => $stationsWithSameIfopt ?? [],
            'nearbyStations' => StationService::getNearbyStations($station),
            'latestCheckins' => StationService::getLatestCheckins($station),
        ]);
    }

    /**
     * !!!! Experimental Backend Function !!!!
     * Fetches the Wikidata information for a station.
     * Try to find matching Wikidata entity for the station and fetch it.
     * Needs to be cleaned up and refactored, if it should be used consistently.
     * Little testing if it works as expected.
     */
    public function fetchWikidata(int $id): JsonResponse
    {
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
     * @throws AuthorizationException
     *
     * @todo Make this an API endpoint when it is accessible for users too
     */
    public function importWikidata(Request $request): RedirectResponse
    {
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

    public function TrainAutocomplete(string $station): JsonResponse
    {
        try {
            $provider = new \App\Http\Controllers\Backend\Transport\StationController();
            $trainAutocompleteResponse = $provider->search($station);

            return response()->json(StationResource::collection($trainAutocompleteResponse));
        } catch (DataProviderException $e) {
            abort(503, $e->getMessage());
        }
    }
}
