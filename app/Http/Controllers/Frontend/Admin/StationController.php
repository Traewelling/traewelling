<?php

namespace App\Http\Controllers\Frontend\Admin;

use App\Dto\Coordinate;
use App\Http\Controllers\Controller;
use App\Models\Station;
use App\Models\StationName;
use App\Objects\LineSegment;
use App\Services\Wikidata\WikidataImportService;
use App\Services\Wikidata\WikidataQueryService;
use Illuminate\Auth\Access\AuthorizationException;
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
    public function fetchWikidata(int $id): void {
        $station = Station::findOrFail($id);
        $this->authorize('update', $station);

        // P054 = IBNR
        $sparqlQuery = <<<SPARQL
            SELECT ?item WHERE { ?item wdt:P954 "{$station->ibnr}". }
        SPARQL;

        $objects = (new WikidataQueryService())->setQuery($sparqlQuery)->execute()->getObjects();
        if (count($objects) > 1) {
            Log::debug('More than one object found for station ' . $station->ibnr . ' (' . $station->id . ') - skipping');
            return;
        }

        if (empty($objects)) {
            Log::debug('No object found for station ' . $station->ibnr . ' (' . $station->id . ') - skipping');
            return;
        }

        $object = $objects[0];
        $station->update(['wikidata_id' => $object->qId]);
        Log::debug('Fetched object ' . $object->qId . ' for station ' . $station->name . ' (Trwl-ID: ' . $station->id . ')');

        $ifopt = $object->getClaims('P12393')[0]['mainsnak']['datavalue']['value'] ?? null;
        if ($station->ifopt_a === null && $ifopt !== null) {
            $splitIfopt = explode(':', $ifopt);
            $station->update([
                                 'ifopt_a' => $splitIfopt[0] ?? null,
                                 'ifopt_b' => $splitIfopt[1] ?? null,
                                 'ifopt_c' => $splitIfopt[2] ?? null,
                             ]);
        }

        $rl100 = $object->getClaims('P8671')[0]['mainsnak']['datavalue']['value'] ?? null;
        if ($station->rilIdentifier === null && $rl100 !== null) {
            $station->update(['rilIdentifier' => $rl100]);
        }

        //get names
        foreach ($object->getClaims('P2561') as $property) {
            $text     = $property['mainsnak']['datavalue']['value']['text'] ?? null;
            $language = $property['mainsnak']['datavalue']['value']['language'] ?? null;
            if ($language === null || $text === null) {
                continue;
            }
            StationName::updateOrCreate([
                                            'station_id' => $station->id,
                                            'language'   => $language,
                                        ], [
                                            'name' => $text
                                        ]);
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
