<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Backend\Transport\StationController;
use App\Http\Resources\StationResource;
use Exception;
use Illuminate\Http\JsonResponse;

/**
 * @deprecated Content will be moved to the backend/frontend/API packages soon, please don't add new functions here!
 */
class FrontendTransportController extends Controller
{
    public function TrainAutocomplete(string $station): JsonResponse
    {
        try {
            // todo: adapt data provider to users preferences
            $provider = new StationController();
            $trainAutocompleteResponse = $provider->search($station, 'de');

            return response()->json(StationResource::collection($trainAutocompleteResponse));
        } catch (Exception $e) {
            abort(503, $e->getMessage());
        }
    }
}
