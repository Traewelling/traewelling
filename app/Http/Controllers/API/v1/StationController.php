<?php

namespace App\Http\Controllers\API\v1;

use App\DataProviders\Motis;
use App\Enum\DataProvider;
use App\Http\Controllers\Backend\Transport\StationController as StationBackendController;
use App\Http\Resources\StationResource;
use App\Models\Checkin;
use App\Models\Event;
use App\Models\EventSuggestion;
use App\Models\Station;
use App\Models\StationIdentifier;
use App\Models\Stopover;
use App\Models\Trip;
use App\Repositories\StationRepository;
use App\StationIdentifierType;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\DB;
use Log;
use OpenApi\Attributes as OA;

class StationController extends Controller
{
    private StationRepository $stationRepository;

    public function __construct(StationRepository $stationRepository)
    {
        $this->stationRepository = $stationRepository;
    }

    public function store(Request $request): StationResource
    {
        $this->authorize('create', Station::class);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
        ]);
        $station = Station::create($validated);

        return new StationResource($station);
    }

    public function destroy(int $id): StationResource|JsonResponse
    {
        $station = Station::findOrFail($id);
        $this->authorize('delete', $station);

        if (
            Stopover::where('train_station_id', $station->id)->exists()
            || Event::where('station_id', $station->id)->exists()
            || EventSuggestion::where('station_id', $station->id)->exists()
        ) {
            return $this->sendError('Station is still in use and cannot be deleted', 409);
        }

        $station->delete();

        return $this->sendResponse(true);
    }

    /**
     * Merge two stations. The first station will be the one that is kept, the second one will be deleted.
     *
     *
     * @throws AuthorizationException
     */
    public function merge(int $oldStationId, int $newStationId): StationResource
    {
        $oldStation = Station::findOrFail($oldStationId);
        $newStation = Station::findOrFail($newStationId);

        // check if user is allowed to update and delete stations - because merging is a combination of both
        $this->authorize('update', $oldStation);
        $this->authorize('delete', $oldStation);

        // do this before the transaction, so new checkins will be created with the new station, if transaction fails
        StationIdentifier::where('station_id', $oldStation->id)->update(['station_id' => $newStation->id]);

        DB::transaction(function () use ($newStation, $oldStation) {
            // Before the update: remove duplicates in stopovers
            $potentialDuplicates = Stopover::where('train_station_id', $oldStation->id)->get();

            foreach ($potentialDuplicates as $oldStopover) {
                // check if the dates are the same (then it can be safely removed)
                $duplicate = Stopover::where('train_station_id', $newStation->id)
                    ->where('trip_id', $oldStopover->trip_id)
                    ->where('departure_planned', $oldStopover->departure_planned)
                    ->where('arrival_planned', $oldStopover->arrival_planned)
                    ->first();

                if ($duplicate) {
                    // if there is a duplicate: move stopovers and then delete the old one
                    Checkin::where('origin_stopover_id', $oldStopover->id)->update(['origin_stopover_id' => $duplicate->id]);
                    Checkin::where('destination_stopover_id', $oldStopover->id)->update(['destination_stopover_id' => $duplicate->id]);
                    $oldStopover->delete();
                }
            }

            Stopover::where('train_station_id', $oldStation->id)->update(['train_station_id' => $newStation->id]);
            Trip::where('origin_id', $oldStation->id)->update(['origin_id' => $newStation->id]);
            Trip::where('destination_id', $oldStation->id)->update(['destination_id' => $newStation->id]);
            Event::where('station_id', $oldStation->id)->update(['station_id' => $newStation->id]);
            EventSuggestion::where('station_id', $oldStation->id)->update(['station_id' => $newStation->id]);

            $oldStation->delete();

            if ($newStation->isDirty()) {
                $newStation->save();
            }
        });

        $logMessage = 'Merged station ' . $oldStation->name . ' (' . $oldStation->id . ') into ' . $newStation->name . ' (' . $newStation->id . ')';
        activity()->causedBy(auth()->user())
            ->performedOn($oldStation)
            ->log($logMessage);
        activity()->causedBy(auth()->user())
            ->performedOn($newStation)
            ->log($logMessage);

        return new StationResource($newStation);
    }

    public function update(Request $request, int $id): StationResource
    {
        $station = Station::findOrFail($id);
        $this->authorize('update', $station);

        $validated = $request->validate([
            'name' => ['nullable', 'string', 'max:255'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'time_offset' => ['nullable', 'numeric'],
        ]);

        if (array_key_exists('time_offset', $request->json()->all()) && $request->json('time_offset') === null) {
            $validated['time_offset'] = null;
        }

        $station->update($validated);

        return new StationResource($station);
    }

    #[OA\Get(
        path: '/stations',
        operationId: 'indexStation',
        description: 'UNSTABLE: Returns stations by fuzzy text, exact identifier, or within a bounding box (BBOX). **CAUTION:** Slashes in {query} must be replaced (e.g. with %20).',
        summary: 'Search for stations',
        security: [['passport' => ['create-statuses']], ['token' => []]],
        tags: ['Checkin'],
        parameters: [
            new OA\Parameter(
                name: 'query',
                description: 'Fuzzy station search',
                in: 'query',
                schema: new OA\Schema(type: 'string', maxLength: 255),
                example: 'Karlsruhe Hbf',
            ),
            new OA\Parameter(
                name: 'identifier_provider',
                description: 'Identifier provider for exact lookup',
                in: 'query',
                schema: new OA\Schema(type: 'string', enum: ['ibnr', 'transitous']),
                example: 'ibnr',
            ),
            new OA\Parameter(
                name: 'identifier',
                description: 'Station identifier for exact lookup',
                in: 'query',
                schema: new OA\Schema(type: 'string', maxLength: 255),
                example: '8000191',
            ),
            new OA\Parameter(
                name: 'min_lat',
                description: 'Minimum latitude of BBOX (WGS84, -90..90)',
                in: 'query',
                schema: new OA\Schema(type: 'number', format: 'float'),
                example: 48.90,
            ),
            new OA\Parameter(
                name: 'max_lat',
                description: 'Maximum latitude of BBOX (WGS84, -90..90)',
                in: 'query',
                schema: new OA\Schema(type: 'number', format: 'float'),
                example: 49.10,
            ),
            new OA\Parameter(
                name: 'min_lon',
                description: 'Minimum longitude of BBOX (WGS84, -180..180)',
                in: 'query',
                schema: new OA\Schema(type: 'number', format: 'float'),
                example: 8.20,
            ),
            new OA\Parameter(
                name: 'max_lon',
                description: 'Maximum longitude of BBOX (WGS84, -180..180)',
                in: 'query',
                schema: new OA\Schema(type: 'number', format: 'float'),
                example: 8.60,
            ),
            new OA\Parameter(
                name: 'limit',
                description: 'Maximum number of results (capped at 100).',
                in: 'query',
                schema: new OA\Schema(type: 'integer', maximum: 100, minimum: 1),
                example: 50,
            ),
            new OA\Parameter(
                name: 'withIdentifiers',
                description: 'Include station identifiers in the response.',
                in: 'query',
                schema: new OA\Schema(type: 'boolean'),
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'successful operation',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'data',
                            type: 'array',
                            items: new OA\Items(ref: '#/components/schemas/StationResource'),
                        ),
                    ],
                ),
            ),
            new OA\Response(response: 401, description: 'Unauthorized'),
            new OA\Response(response: 404, description: 'Station not found'),
            new OA\Response(
                response: 503,
                description: 'There has been an error with our data provider',
            ),
        ],
    )]
    public function index(Request $request): AnonymousResourceCollection|JsonResponse
    {
        $validated = $request->validate([
            // option 1: fuzzy search
            'query' => ['required_without_all:identifier,identifier_provider,min_lat,max_lat,min_lon,max_lon', 'string', 'max:255'],

            // option 2: exact search per Identifier
            'identifier_provider' => ['required_without_all:query,min_lat,max_lat,min_lon,max_lon', 'string', 'in:ibnr,transitous'],
            'identifier' => ['required_without_all:query,min_lat,max_lat,min_lon,max_lon', 'string', 'max:255'],

            // option 3: BBOX-Koordinaten
            'min_lat' => ['sometimes', 'numeric', 'between:-90,90'],
            'max_lat' => ['sometimes', 'numeric', 'between:-90,90'],
            'min_lon' => ['sometimes', 'numeric', 'between:-180,180'],
            'max_lon' => ['sometimes', 'numeric', 'between:-180,180'],

            'limit' => ['sometimes', 'integer', 'min:1', 'max:250'],
        ]);

        $withIdentifiers = $request->boolean('withIdentifiers');

        $hasAnyBboxParam = $request->filled('min_lat') || $request->filled('max_lat') || $request->filled('min_lon') || $request->filled('max_lon');

        if ($hasAnyBboxParam) {
            $request->validate([
                'min_lat' => ['required', 'numeric', 'between:-90,90'],
                'max_lat' => ['required', 'numeric', 'between:-90,90'],
                'min_lon' => ['required', 'numeric', 'between:-180,180'],
                'max_lon' => ['required', 'numeric', 'between:-180,180'],
            ]);

            $minLat = (float) $request->input('min_lat');
            $maxLat = (float) $request->input('max_lat');
            $minLon = (float) $request->input('min_lon');
            $maxLon = (float) $request->input('max_lon');

            // make sure min < max
            if ($minLat > $maxLat) {
                [$minLat, $maxLat] = [$maxLat, $minLat];
            }
            if ($minLon > $maxLon) {
                [$minLon, $maxLon] = [$maxLon, $minLon];
            }

            $limit = min(max((int) $request->input('limit', 250), 1), 250);

            // get stations within BBOX from database
            $query = Station::whereBetween('latitude', [$minLat, $maxLat])
                ->whereBetween('longitude', [$minLon, $maxLon])
                ->orderByDesc('relevance')
                ->orderBy('name')
                ->limit($limit);

            if ($withIdentifiers) {
                $query->with('stationIdentifiers');
            }

            return StationResource::collection($query->get());
        }

        // fuzzy search
        if (array_key_exists('query', $validated)) {
            $stations = (new StationBackendController())->search($validated['query']);

            if ($withIdentifiers) {
                $stations->loadMissing('stationIdentifiers');
            }

            return StationResource::collection($stations);
        }

        // exact search by identifier
        $identifier = $validated['identifier'] ?? null;
        $provider = $validated['identifier_provider'] ?? null;
        $station = null;

        if ($provider === 'ibnr') {
            $station = $this->stationRepository->getStationByIbnr($identifier);
        }

        if ($provider === 'transitous') {
            try {
                $station = $this->stationRepository->getStationByIdentifier(
                    $identifier,
                    StationIdentifierType::MOTIS,
                    $provider
                ) ?? (new Motis(DataProvider::TRANSITOUS))->fetchStationFromApi($identifier);
            } catch (\Exception $e) {
                report($e);
                Log::error('Error fetching station from Transitous: ' . $e->getMessage());

                return $this->sendError('Error fetching station from Transitous', 503);
            }
        }

        if (!$station) {
            return response()->json(null, 404);
        }

        if ($withIdentifiers) {
            $station->loadMissing('stationIdentifiers');
        }

        return StationResource::collection([new StationResource($station)]);
    }

    #[OA\Get(
        path: '/stations/{id}',
        operationId: 'showStation',
        description: 'This request returns a single station object',
        summary: 'Show station',
        security: [['passport' => ['create-statuses']], ['token' => []]],
        tags: ['Checkin'],
        parameters: [new OA\Parameter(name: 'id', description: 'station id', in: 'path', example: '1337')],
        responses: [
            new OA\Response(
                response: 200,
                description: 'successful operation',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'data',
                            ref: '#/components/schemas/StationResource',
                            type: 'object',
                        ),
                    ],
                ),
            ),
            new OA\Response(response: 401, description: 'Unauthorized'),
            new OA\Response(
                response: 503,
                description: 'There has been an error with our data provider',
            ),
        ],
    )]
    public function show(int $id): JsonResponse
    {
        $station = Station::findOrFail($id);

        return $this->sendResponse(new StationResource($station));
    }
}
