<?php

namespace App\Http\Controllers;

use App\DataProviders\DataProviderBuilder;
use App\DataProviders\DataProviderInterface;
use App\Http\Controllers\API\v1\ExperimentalController;
use App\Http\Resources\StationResource;
use App\Models\Checkin;
use App\Models\PolyLine;
use App\Models\Station;
use App\Models\User;
use App\Services\Wikidata\WikidataImportService;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

/**
 * @deprecated Content will be moved to the backend/frontend/API packages soon, please don't add new functions here!
 */
class TransportController extends Controller
{
    private DataProviderInterface $dataProvider;

    /**
     * @template T of DataProviderInterface
     * @param class-string<T> $dataProvider
     */
    public function __construct(string $dataProvider) {
        $this->dataProvider = (new DataProviderBuilder())->build($dataProvider);
    }

    /**
     * @param string $query
     *
     * @return Collection
     * @api v1
     */
    public function getTrainStationAutocomplete(string $query): Collection {
        if (!is_numeric($query) && strlen($query) <= 5 && ctype_upper($query)) {
            $stations = $this->dataProvider->getStationsByFuzzyRilIdentifier(rilIdentifier: $query);
        } elseif (preg_match('/^Q\d+$/', $query)) {
            $stations = self::getStationsByWikidataId($query);
        } elseif (!isset($stations) || $stations[0] === null) {
            $stations = $this->dataProvider->getStations($query);
        }
        return $stations->map(function(Station $station) {
            return new StationResource($station);
        });
    }

    private static function getStationsByWikidataId(string $wikidataId): Collection {
        $stations = Station::where('wikidata_id', $wikidataId)->get();

        if ($stations->isEmpty() && ExperimentalController::checkGeneralRateLimit() && ExperimentalController::checkWikidataIdRateLimit($wikidataId)) {
            try {
                Log::debug('Lookup Wikidata ID as User searched it', ['wikidataId' => $wikidataId]);
                $station = WikidataImportService::importStation($wikidataId);
                Log::info('Saved Station from Wikidata.', [$station->only(['id', 'name', 'wikidata_id'])]);
                $stations->push($station);
            } catch (\InvalidArgumentException $exception) {
                // ignore in frontend, just log for debugging
                Log::debug('Could not import Station from Wikidata: ' . $exception->getMessage(), ['wikidataId' => $wikidataId]);
            } catch (\Exception $exception) {
                report($exception);
            }
        }

        return $stations;
    }

    /**
     * Check if there are colliding CheckIns
     *
     * @param User   $user
     * @param Carbon $start
     * @param Carbon $end
     *
     * @return Collection
     * @see https://stackoverflow.com/questions/53697172/laravel-eloquent-query-to-check-overlapping-start-and-end-datetime-fields/53697498
     */
    public static function getOverlappingCheckIns(User $user, Carbon $start, Carbon $end): Collection {
        //increase the tolerance for start and end of collisions
        $start = $start->clone()->addMinutes(10);
        $end   = $end->clone()->subMinutes(10);

        if ($end->isBefore($start)) {
            return collect();
        }

        $checkInsToCheck = Checkin::with(['Trip.stopovers', 'originStopover.station.names', 'destinationStopover.station.names'])
                                  ->join('statuses', 'statuses.id', '=', 'train_checkins.status_id')
                                  ->where('statuses.user_id', $user->id)
                                  ->where('departure', '>=', $start->clone()->subDays(3))
                                  ->get();

        return $checkInsToCheck->filter(function(Checkin $checkin) use ($start, $end) {
            //use realtime-data or use planned if not available
            $departure = $checkin?->originStopover?->departure ?? $checkin->departure;
            $arrival   = $checkin?->destinationStopover?->arrival ?? $checkin->arrival;

            return (
                       $arrival->isAfter($start) &&
                       $departure->isBefore($end)
                   ) || (
                       $arrival->isAfter($end) &&
                       $departure->isBefore($start)
                   ) || (
                       $departure->isAfter($start) &&
                       $arrival->isBefore($start)
                   );
        });
    }

    /**
     * Get the PolyLine Model from Database
     *
     * @param string $polyline The Polyline as a json string given by hafas
     *
     * @return PolyLine
     */
    public static function getPolylineHash(string $polyline): PolyLine {
        return PolyLine::updateOrCreate([
                                            'hash' => md5($polyline)
                                        ], [
                                            'polyline' => $polyline
                                        ]);
    }
}
