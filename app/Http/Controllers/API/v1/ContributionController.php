<?php

namespace App\Http\Controllers\API\v1;

use App\Http\Controllers\Backend\Admin\EventController as AdminEventBackend;
use App\Http\Controllers\Backend\ContributionController as ContributionBackend;
use App\Http\Controllers\HafasController;
use App\Http\Resources\ContributionEventSuggestionResource;
use App\Models\Event;
use App\Models\EventSuggestion;
use App\Notifications\EventSuggestionProcessed;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use OpenApi\Annotations as OA;

class ContributionController extends Controller
{
    /**
     * @OA\Get(
     *     path="/contribution/events/getSuggestion",
     *     operationId="contributionGetEventSuggestion",
     *     tags={"Contribution"},
     *     summary="Get the next event suggestion to be approved",
     *     description="Returns an event suggestion to be moderated and approved",
     *     @OA\Response(
     *         response=200,
     *         description="success",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="object",
     *                 ref="#/components/schemas/ContributionEventSuggestion"
     *             )
     *         ),
     *     ),
     *     @OA\Response(response=404, description="Currently no event suggestions available to fetch"),
     *     @OA\Response(response=403, description="User does not have the rights for this route"),
     *     security={
     *         {"passport": {"contribution"}}
     *     }
     * )
     */
    public function getSuggestion() {
        return new ContributionEventSuggestionResource(ContributionBackend::getEventSuggestion());
    }

    /**
     * @OA\Post(
     *      path="/contribution/event/approve",
     *      operationId="submitEventModeration",
     *      tags={"Contribution"},
     *      summary="Submit a moderated event suggestion",
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(ref="#/components/schemas/ContributionEventSuggestion")
     *      ),
     *      @OA\Response(
     *          response=201,
     *          description="successful operation",
     *       ),
     *       @OA\Response(response=400, description="Bad request"),
     *       @OA\Response(response=403, description="User not authorized"),
     *       security={{"passport": {}}}
     *     )
     *
     *
     */
    public function approveSuggestion(Request $request) {
        $validated = $request->validate([
                                            'id'             => ['required', 'exists:event_suggestions,id'],
                                            'name'           => ['required', 'max:255'],
                                            'hashtag'        => ['required', 'max:30'],
                                            'host'           => ['required', 'max:255'],
                                            'url'            => ['nullable', 'url'],
                                            'nearestStation' => ['nullable', 'max:255'],
                                            'begin'          => ['required', 'date'],
                                            'end'            => ['required', 'date'],
                                            'checkinBegin'   => ['nullable', 'date', 'before_or_equal:begin'],
                                            'checkinEnd'     => ['nullable', 'date', 'after_or_equal:end'],
                                        ]);

        $eventSuggestion = EventSuggestion::where('id', $validated['id'])->where('processed', false)->firstOrFail();
        $trainStation    = null;

        if (isset($validated['nearestStation'])) {
            $trainStation = HafasController::getStations($validated['nearestStation'], 1)->first();

            if ($trainStation === null) {
                return $this->sendError('Trainstation not found', 400);
            }
        }

        $checkinBegin = $validated['checkinBegin'] ?? $validated['begin'];
        $checkinEnd   = $validated['checkinEnd'] ?? $validated['end'];

        $event = Event::create([
                                   'name'        => $validated['name'],
                                   'slug'        => AdminEventBackend::createSlugFromName($validated['name']),
                                   'hashtag'     => $validated['hashtag'],
                                   'host'        => $validated['host'],
                                   'station_id'  => $trainStation?->id,
                                   'begin'       => Carbon::parse($checkinBegin)->toIso8601String(),
                                   'end'         => Carbon::parse($checkinEnd)->toIso8601String(),
                                   'event_start' => $validated['checkinBegin'] ?
                                       Carbon::parse($validated['begin'])->toIso8601String() : null,
                                   'event_end'   => $validated['checkinEnd'] ?
                                       Carbon::parse($validated['end'])->toIso8601String() : null,
                                   'url'         => $validated['url'] ?? null,
                                   'accepted_by' => auth()->user()->id,
                                   'approved'    => (auth()->user()->moderation_reputation > 50)
                               ]);

        $eventSuggestion->update(['processed' => true]);
        /**
         * I'm not entirely sure about webhooks and how they work so I'm commenting this out for now

        if (!App::runningUnitTests() && config('app.admin.webhooks.new_event') !== null) {
            Http::post(config('app.admin.webhooks.new_event'), [
                'content' => auth()->user()->name . ' accepted the event "' . $eventSuggestion->name . '".',
            ]);
        }
         * */

        if (auth()->user()->moderation_reputation > 50) {
            $eventSuggestion->user->notify(new EventSuggestionProcessed($eventSuggestion, $event));
        }

        return $this->sendResponse();
    }


    /**
     * @OA\Post(
     *      path="/contribution/event/deny",
     *      operationId="denyEventModeration",
     *      tags={"Contribution"},
     *      summary="Deny a moderated event suggestion",
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(
     *              @OA\Property(
     *                  property="id",
     *                  type="integer",
     *                  example=1234
     *              ),
     *              @OA\Property(
     *                  property="reason",
     *                  type="string",
     *                  enum={"duplicate", "too-late", "not-applicable", "denied"},
     *                  example="not-applicable"
     *              )
     *          )
     *      ),
     *      @OA\Response(
     *          response=201,
     *          description="successful operation",
     *       ),
     *       @OA\Response(response=400, description="Bad request"),
     *       @OA\Response(response=403, description="User not authorized"),
     *       security={{"passport": {}}}
     *     )
     *
     *
     */
    public function denySuggestion() {
        return "3";
    }
}
