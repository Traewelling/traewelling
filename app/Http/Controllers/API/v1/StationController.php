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

class StationController extends Controller {
    private StationRepository $stationRepository;

    public function __construct(StationRepository $stationRepository) {
        $this->stationRepository = $stationRepository;
    }

    public function store(Request $request): StationResource {
        $this->authorize('create', Station::class);

        $validated = $request->validate([
                                            'ibnr'          => ['nullable', 'numeric', 'unique:train_stations'],
                                            'rilIdentifier' => ['nullable', 'string', 'max:10'],
                                            'name'          => ['required', 'string', 'max:255'],
                                            'latitude'      => ['required', 'numeric', 'between:-90,90'],
                                            'longitude'     => ['required', 'numeric', 'between:-180,180'],
                                        ]);
        $station   = Station::create($validated);
        return new StationResource($station);
    }

    public function destroy(int $id): StationResource|JsonResponse {
        $station = Station::findOrFail($id);
        $this->authorize('delete', $station);

        if(
            Stopover::where('train_station_id', $station->id)->exists()
            || Event::where('station_id', $station->id)->exists()
            || EventSuggestion::where('station_id', $station->id)->exists()
            || Checkin::where('origin', $station->ibnr)->orWhere('destination', $station->ibnr)->exists()
            || Trip::where('origin', $station->ibnr)->orWhere('destination', $station->ibnr)->exists()
        ) {
            return $this->sendError('Station is still in use and cannot be deleted', 409);
        }

        $station->delete();
        return $this->sendResponse(true);
    }

    /**
     * Merge two stations. The first station will be the one that is kept, the second one will be deleted.
     *
     * @param int $oldStationId
     * @param int $newStationId
     *
     * @return StationResource
     * @throws AuthorizationException
     */
    public function merge(int $oldStationId, int $newStationId): StationResource {
        $oldStation = Station::findOrFail($oldStationId);
        $newStation = Station::findOrFail($newStationId);

        // check if user is allowed to update and delete stations - because merging is a combination of both
        $this->authorize('update', $oldStation);
        $this->authorize('delete', $oldStation);

        // do this before the transaction, so new checkins will be created with the new station, if transaction fails
        StationIdentifier::where('station_id', $oldStation->id)->update(['station_id' => $newStation->id]);

        DB::transaction(function() use ($newStation, $oldStation) {
            // Before the update: remove duplicates in stopovers
            $potentialDuplicates = Stopover::where('train_station_id', $oldStation->id)->get();

            foreach($potentialDuplicates as $oldStopover) {
                // check if the dates are the same (then it can be safely removed)
                $duplicate = Stopover::where('train_station_id', $newStation->id)
                                     ->where('trip_id', $oldStopover->trip_id)
                                     ->where('departure_planned', $oldStopover->departure_planned)
                                     ->where('arrival_planned', $oldStopover->arrival_planned)
                                     ->first();

                if($duplicate) {
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

            // merge columns from old->new if they are null
            $columns = ['ibnr', 'wikidata_id', 'rilIdentifier', 'ifopt_a', 'ifopt_b', 'ifopt_c', 'ifopt_d', 'ifopt_e'];
            foreach($columns as $column) {
                if($newStation->{$column} === null && $oldStation->{$column} !== null) {
                    $newStation->{$column} = $oldStation->{$column};
                }
            }

            $oldStation->delete();

            // save AFTER deletion to avoid foreign key constraint errors
            if($newStation->isDirty()) {
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

    public function update(Request $request, int $id): StationResource {
        $station = Station::findOrFail($id);
        $this->authorize('update', $station);

        $validated = $request->validate([
                                            'ibnr'          => ['nullable', 'numeric', 'unique:train_stations,ibnr,' . $station->id],
                                            'rilIdentifier' => ['nullable', 'string', 'max:10'],
                                            'name'          => ['nullable', 'string', 'max:255'],
                                            'latitude'      => ['nullable', 'numeric', 'between:-90,90'],
                                            'longitude'     => ['nullable', 'numeric', 'between:-180,180'],
                                            'time_offset'   => ['nullable', 'numeric'],
                                        ]);

        if(array_key_exists('time_offset', $request->json()->all()) && $request->json('time_offset') === null) {
            $validated['time_offset'] = null;
        }

        $station->update($validated);
        return new StationResource($station);
    }

    /**
     * @OA\Get(
     *      path="/stations",
     *      operationId="indexStation",
     *      tags={"Checkin"},
     *      summary="Search for stations",
     *      description="UNSTABLE: This request returns an array of max. 20 station objects matching the query. **CAUTION:** All
     *      slashes (as well as encoded to %2F) in {query} need to be replaced, preferrably by a space (%20)",
     * @OA\Parameter(
     *          name="query",
     *          in="query",
     *          description="station query",
     *          example="Karls"
     *     ),
     * @OA\Parameter(
     *     name="identifier_provider",
     *     in="query",
     *     description="identifier provider",
     *     example="ibnr",
     *     @OA\Schema(
     *     type="string",
     *     enum={"ibnr", "transitous"}
     *     )
     *    ),
     * @OA\Parameter(
     *     name="identifier",
     *     in="query",
     *     description="station identifier",
     *     example="1337",
     *     @OA\Schema(
     *     type="string",
     *     maxLength=255
     *     )
     *   ),
     * @OA\Response(
     *          response=200,
     *          description="successful operation",
     *          @OA\JsonContent(
     *              @OA\Property(property="data", type="array",
     *                  @OA\Items(
     *                      ref="#/components/schemas/StationResource"
     *                  )
     *              )
     *          )
     *       ),
     * @OA\Response(response=401, description="Unauthorized"),
     * @OA\Response(response=503, description="There has been an error with our data provider"),
     *       security={
     *          {"passport": {"create-statuses"}}, {"token": {}}
     *
     *       }
     *     )
     */
    public function index(Request $request): AnonymousResourceCollection|JsonResponse {
        $validated = $request->validate([
                                            // Query = Fuzzy Search
                                            'query'               => ['required_without:identifier,identifier_provider', 'string', 'max:255'],

                                            // If query is not given, we search by identifier (exact match)
                                            'identifier_provider' => ['required_without:query', 'string', 'in:ibnr,transitous'],
                                            'identifier'          => ['required_without:query', 'string', 'max:255'],
                                        ]);

        if(array_key_exists('query', $validated)) {
            $stations = (new StationBackendController())->search($validated['query']);
            return StationResource::collection($stations);
        }

        $identifier = $validated['identifier'];
        $provider   = $validated['identifier_provider'];
        $station    = null;

        if($provider === 'ibnr') {
            $station = $this->stationRepository->getStationByIbnr($identifier);
        }

        if($provider === 'transitous') {
            try {
                $station = $this->stationRepository->getStationByIdentifier($identifier, StationIdentifierType::MOTIS, $provider)
                           ?? (new Motis(DataProvider::TRANSITOUS))->fetchStationFromApi($identifier);
            } catch(\Exception $e) {
                return $this->sendError('Error fetching station from Transitous: ' . $e->getMessage(), 503);
            }
        }

        if(!$station) {
            abort(404, 'Station not found');
        }

        return StationResource::collection([new StationResource($station)]);
    }


    /**
     * @OA\Get(
     *      path="/stations/{id}",
     *      operationId="showStation",
     *      tags={"Checkin"},
     *      summary="Show station",
     *      description="This request returns a single station object",
     *      @OA\Parameter(
     *          name="id",
     *          in="path",
     *          description="station id",
     *          example="1337"
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="successful operation",
     *          @OA\JsonContent(
     *              @OA\Property(property="data", type="object", ref="#/components/schemas/StationResource")
     *          )
     *      ),
     *      @OA\Response(response=401, description="Unauthorized"),
     *      @OA\Response(response=503, description="There has been an error with our data provider"),
     *      security={
     *          {"passport": {"create-statuses"}}, {"token": {}}
     *      }
     *     )
     */
    public function show(int $id): JsonResponse {
        $station = Station::findOrFail($id);

        return $this->sendResponse(new StationResource($station));
    }
}
