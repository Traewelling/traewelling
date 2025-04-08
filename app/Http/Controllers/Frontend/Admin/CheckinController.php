<?php

namespace App\Http\Controllers\Frontend\Admin;

use App\DataProviders\DataProviderBuilder;
use App\Enum\Business;
use App\Enum\StatusVisibility;
use App\Enum\TravelType;
use App\Exceptions\CheckInCollisionException;
use App\Exceptions\HafasException;
use App\Exceptions\StationNotOnTripException;
use App\Exceptions\TrainCheckinAlreadyExistException;
use App\Http\Controllers\Backend\Transport\TrainCheckinController;
use App\Hydrators\CheckinRequestHydrator;
use App\Models\Event;
use App\Models\Station;
use App\Models\Status;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Enum;
use Illuminate\View\View;
use JetBrains\PhpStorm\ArrayShape;
use Throwable;

class CheckinController
{
    /**
     * @throws HafasException
     * @throws ModelNotFoundException
     * @deprecated adapt admin panel to api endpoints
     */
    public static function lookupStation(string|int $query): Station {
        $dataProvider = (new DataProviderBuilder)->build();

        //Lookup by station ibnr
        if (is_numeric($query)) {
            $station = Station::where('ibnr', $query)->first();
            if ($station !== null) {
                return $station;
            }
        }

        //Lookup by ril identifier
        if (!is_numeric($query) && strlen($query) <= 5 && ctype_upper($query)) {
            $station = $dataProvider->getStationByRilIdentifier($query);
            if ($station !== null) {
                return $station;
            }
        }

        //Lookup HAFAS
        $station = $dataProvider->getStations(query: $query, results: 1)->first();
        if ($station !== null) {
            return $station;
        }

        throw new ModelNotFoundException;
    }

    /**
     * @param string|int      $stationQuery
     * @param Carbon|null     $when
     * @param TravelType|null $travelType
     * @param bool            $localtime
     *
     * @return array
     * @throws HafasException
     * @deprecated use DataProviderInterface->getDepartures(...) directly instead (-> less overhead)
     */
    #[ArrayShape([
        'station'    => Station::class,
        'departures' => Collection::class,
        'times'      => "array"
    ])]
    public static function getDeprecatedDepartures(
        string|int  $stationQuery,
        ?Carbon     $when = null,
        ?TravelType $travelType = null,
        bool        $localtime = false
    ): array {
        $station = self::lookupStation($stationQuery);

        $when  = $when ?? Carbon::now()->subMinutes(5);
        $times = [
            'now'  => $when,
            'prev' => $when->clone()->subMinutes(15),
            'next' => $when->clone()->addMinutes(15)
        ];

        $departures = (new DataProviderBuilder)->build()->getDepartures(
            station:   $station,
            when:      $when,
            type:      $travelType,
            localtime: $localtime
        )->sortBy(function($departure) {
            return $departure->when ?? $departure->plannedWhen;
        });

        return ['station' => $station, 'departures' => $departures->values(), 'times' => $times];
    }

    public function renderStationboard(Request $request): View|RedirectResponse {
        $validated = $request->validate([
                                            'station'   => ['nullable'],
                                            'when'      => ['nullable', 'date'],
                                            'filter'    => ['nullable', new Enum(TravelType::class)],
                                            'userQuery' => ['nullable']
                                        ]);

        $user = Auth::user();
        if (isset($validated['userQuery'])) {
            try {
                if (is_numeric($validated['userQuery'])) {
                    $user = User::findOrFail($validated['userQuery']);
                } else {
                    $user = User::where('username', 'like', '%' . $validated['userQuery'] . '%')->firstOrFail();
                }
            } catch (ModelNotFoundException) {
                return redirect()->back()->withErrors("User non-existent");
            }
        }

        $when = isset($validated['when']) ? Carbon::parse($validated['when']) : Carbon::now();

        if (isset($validated['station'])) {
            try {
                $trainStationboardResponse = self::getDeprecatedDepartures(
                    stationQuery: $validated['station'],
                    when:         $when,
                    travelType:   TravelType::tryFrom($validated['filter'] ?? null),
                );

                $station    = $trainStationboardResponse['station'];
                $departures = $trainStationboardResponse['departures'];
                $times      = $trainStationboardResponse['times'];
            } catch (HafasException $exception) {
                return back()->with('error', $exception->getMessage());
            } catch (ModelNotFoundException) {
                return redirect()->back()->with('error', __('controller.transport.no-station-found'));
            }
        }

        $lastStatuses = Status::where('user_id', $user->id)->orderBy('created_at', 'desc')->limit(10)->get();

        return view('admin.checkin.stationboard', [
            'station'      => $station ?? null,
            'departures'   => $departures ?? null,
            'times'        => $times ?? null,
            'when'         => $when,
            'user'         => $user,
            'lastStatuses' => $lastStatuses,
        ]);
    }

    public function renderTrip(string $tripId, Request $request): RedirectResponse|View {
        $validated = $request->validate([
                                            'lineName'  => ['required'],
                                            'start'     => ['required', 'numeric'],
                                            'departure' => ['required', 'date'],
                                            'userId'    => ['nullable', 'numeric']
                                        ]);

        $user = Auth::user();
        if (isset($validated['userId'])) {
            $user = User::find($validated['userId']);
        }

        try {
            $hafasTrip = TrainCheckinController::getHafasTrip(
                tripId:   $tripId,
                lineName: $validated['lineName'],
                startId:  $validated['start'],
            );
            return view('admin.checkin.trip', [
                'hafasTrip' => $hafasTrip,
                'events'    => Event::forTimestamp(now())->get(),
                'stopovers' => $hafasTrip->stopovers,
                'user'      => $user,
            ]);
        } catch (HafasException $exception) {
            return back()->with('alert-danger', $exception->getMessage());
        } catch (StationNotOnTripException) {
            return redirect()->back()->with('alert-danger', __('controller.transport.not-in-stopovers'));
        }
    }

    public function checkin(Request $request): View|RedirectResponse {
        $validated = $request->validate([
                                            'body'                => ['nullable', 'max:280'],
                                            'business'            => ['nullable', new Enum(Business::class)],
                                            'visibility'          => ['nullable', new Enum(StatusVisibility::class)],
                                            'eventId'             => ['nullable', 'integer', 'exists:events,id'],
                                            'toot'                => ['nullable', 'max:2'],
                                            'chainPost'           => ['nullable', 'max:2'],
                                            'tripId'              => ['required'],
                                            'lineName'            => ['required'],
                                            'start'               => ['required', 'numeric'],
                                            'destinationStopover' => ['required', 'exists:train_stopovers,id'],
                                            'departure'           => ['required', 'date'],
                                            'force'               => ['nullable', 'max:2'],
                                            'userId'              => ['required', 'integer'],
                                            'ibnr'                => ['nullable', 'max:2'],
                                        ]);
        try {
            $user = User::findOrFail($validated['userId']);
        } catch (ModelNotFoundException) {
            return redirect()->back()->withErrors('User non-existent');
        }

        try {
            $dto             = (new CheckinRequestHydrator($validated, $user))->hydrateFromAdmin();
            $backendResponse = TrainCheckinController::checkin($dto);

            return redirect()->route('admin.stationboard')
                             ->with('alert-success', 'Checked in successfully. Earned points: ' . $backendResponse->pointCalculation->points);

        } catch (CheckInCollisionException $e) {
            return redirect()
                ->back()
                ->withErrors(__(
                                 'controller.transport.overlapping-checkin',
                                 [
                                     'linename' => $e->checkin->trip->linename
                                 ]
                             ) . strtr(' <a href=":url">#:id</a>',
                                       [
                                           ':url' => url('/status/' . $e->checkin->status->id),
                                           ':id'  => $e->checkin->status->id,
                                       ]
                             ));

        } catch (StationNotOnTripException) {
            return back()->withErrors("station not on trip");
        } catch (HafasException $exception) {
            return back()->withErrors($exception->getMessage());
        } catch (TrainCheckinAlreadyExistException) {
            return back()->withErrors('CheckIn already exists');
        } catch (Throwable $throwed) {
            report($throwed);
            return back()->with('alert-danger', 'Fehler beim Speichern des CheckIns: ' . get_class($throwed) . ' -> ' . $throwed->getMessage());
        }
    }
}
