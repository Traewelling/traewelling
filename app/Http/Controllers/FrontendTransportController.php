<?php

namespace App\Http\Controllers;

use App\Exceptions\HafasException;
use App\Http\Controllers\Backend\Transport\StationController;
use App\Http\Controllers\TransportController as TransportBackend;
use App\Models\Trip;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

/**
 * @deprecated Content will be moved to the backend/frontend/API packages soon, please don't add new functions here!
 */
class FrontendTransportController extends Controller
{
    public function TrainAutocomplete(string $station): JsonResponse {
        try {
            //todo: adapt data provider to users preferences
            $provider                  = new StationController(null, Auth::user());
            $trainAutocompleteResponse = $provider->search($station, 'de');
            return response()->json($trainAutocompleteResponse);
        } catch (HafasException $e) {
            abort(503, $e->getMessage());
        }
    }

    public function getTrip(int $tripId): View {
        return view('trip-info', [
            'trip' => Trip::findOrFail($tripId)
        ]);
    }
}
