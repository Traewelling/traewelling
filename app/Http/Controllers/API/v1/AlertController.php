<?php

namespace App\Http\Controllers\API\v1;

use App\Http\Requests\StoreAlertRequest;
use App\Http\Resources\AlertResource;
use App\Models\Alert;
use App\Models\AlertTranslation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\DB;

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
        $this->authorize('viewAny', Alert::class);
        $now = now()->startOfDay();

        $alerts = Alert::with('translations')
                       ->where('active_from', '<=', $now)
                       ->where(function($query) use ($now) {
                           $query->where('active_until', '>=', $now)
                                 ->orWhereNull('active_until');
                       })
                       ->orderByDesc('active_from')
                       ->orderByDesc('active_until')
                       ->get();

        // if year in review is active, inject the special alert
        if (config('trwl.year_in_review.alert')) {
            $alert               = new Alert();
            $alert->id           = 'year-in-review-' . date('Y');
            $alert->type         = 'info';
            $alert->active_from  = now()->startOfYear();
            $alert->active_until = now()->endOfYear();

            $translation          = new AlertTranslation();
            $translation->locale  = app()->getLocale();
            $translation->title   = __('year-review');
            $translation->content = __('year-review.teaser');
            $translation->url     = url('/your-year/');
            $alert->setRelation('translations', collect([$translation]));

            $alerts->prepend($alert);
        }

        return AlertResource::collection($alerts);
    }

    /**
     * No documentation, as this is not a public endpoint.
     */
    public function store(StoreAlertRequest $request): AlertResource {
        $this->authorize('create', Alert::class);
        $alert = new Alert();
        $this->updateOrCreate($request, $alert);
        return new AlertResource($alert);
    }

    /**
     * No documentation, as this is not a public endpoint.
     */
    public function update(StoreAlertRequest $request, string $id): AlertResource {
        $alert = Alert::findOrFail($id);
        $this->authorize('update', $alert);
        $this->updateOrCreate($request, $alert);
        return new AlertResource($alert);
    }

    /**
     * No documentation, as this is not a public endpoint.
     */
    public function destroy(Alert $alert): JsonResponse {
        $this->authorize('delete', $alert);
        $alert->delete();
        return response()->json(null, 204);
    }

    private function updateOrCreate(StoreAlertRequest $request, Alert $alert): void {
        DB::beginTransaction();
        $alert->type         = $request->type;
        $alert->active_from  = $request->active_from;
        $alert->active_until = $request->active_until;
        $alert->url          = $request->url;
        $alert->save();

        $alert->translations()->updateOrCreate(
            ['locale' => 'de'],
            [
                'title'   => $request->title_de,
                'content' => $request->content_de,
                'url'     => $request->url_de,
            ]
        );

        $alert->translations()->updateOrCreate(
            ['locale' => 'en'],
            [
                'title'   => $request->title_en,
                'content' => $request->content_en,
                'url'     => $request->url_en,
            ]
        );

        DB::commit();
    }
}
