<?php

namespace App\Http\Controllers\API\v1;

use App\Http\Resources\AlertResource;
use App\Models\Alert;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use OpenApi\Annotations as OA;

class AlertController extends Controller
{
    /**
     * @OA\Get(
     *     path="/alerts",
     *     summary="Get all active alerts",
     *     operationId="getActiveAlerts",
     *     tags={"Notifications"},
     *     @OA\Response(response=200, description="List of active alerts",@OA\JsonContent(
     *         @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/AlertResource"),)
     *     ))
     * )
     */
    public function index(): AnonymousResourceCollection {
        $now = now()->startOfDay();

        $alerts = Alert::with('translations')
                       ->where('active_from', '<=', $now)
                       ->where(function($query) use ($now) {
                           $query->where('active_until', '>=', $now)
                                 ->orWhereNull('active_until');
                       })
                       ->orderBy('active_from', 'desc')
                       ->orderBy('active_until', 'desc')
                       ->get();

        return AlertResource::collection($alerts);
    }
}
