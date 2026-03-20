<?php

namespace App\Http\Controllers\Frontend\Admin;

use App\Exceptions\DataProviderException;
use App\Exceptions\Wikidata\FetchException;
use App\Http\Controllers\Controller;
use App\Http\Resources\StationResource;
use App\Models\Station;
use App\Services\Wikidata\WikidataImportService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class StationController extends Controller
{
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
