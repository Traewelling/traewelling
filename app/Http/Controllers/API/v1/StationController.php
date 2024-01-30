<?php

namespace App\Http\Controllers\API\v1;

use App\Http\Resources\StationResource;
use App\Models\Checkin;
use App\Models\Event;
use App\Models\EventSuggestion;
use App\Models\Station;
use App\Models\Stopover;
use App\Models\Trip;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StationController extends Controller
{
    public function store(Request $request): StationResource {
        $this->authorize('create', Station::class);

        $validated = $request->validate([
                                            'ibnr'          => ['required', 'numeric', 'unique:train_stations'],
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

        if (
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

        $logMessage = 'Merged station ' . $oldStation->name . ' (' . $oldStation->id . ') into ' . $newStation->name . ' (' . $newStation->id . ')';
        activity()->causedBy(auth()->user())
                  ->performedOn($oldStation)
                  ->log($logMessage);
        activity()->causedBy(auth()->user())
                  ->performedOn($newStation)
                  ->log($logMessage);

        Checkin::where('origin', $oldStation->ibnr)->update(['origin' => $newStation->ibnr]);
        Checkin::where('destination', $oldStation->ibnr)->update(['destination' => $newStation->ibnr]);
        Stopover::where('train_station_id', $oldStation->id)->update(['train_station_id' => $newStation->id]);
        Trip::where('origin', $oldStation->ibnr)->update(['origin' => $newStation->ibnr]);
        Trip::where('destination', $oldStation->ibnr)->update(['destination' => $newStation->ibnr]);
        Event::where('station_id', $oldStation->id)->update(['station_id' => $newStation->id]);
        EventSuggestion::where('station_id', $oldStation->id)->update(['station_id' => $newStation->id]);

        $oldStation->delete();

        return new StationResource($newStation);
    }
}
