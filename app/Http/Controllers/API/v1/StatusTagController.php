<?php

namespace App\Http\Controllers\API\v1;

use App\Enum\StatusTagKey;
use App\Enum\StatusVisibility;
use App\Events\StatusUpdateEvent;
use App\Http\Controllers\Backend\Transport\StatusTagController as StatusTagBackend;
use App\Http\Resources\StatusTagResource;
use App\Models\Status;
use App\Models\StatusTag;

use function auth;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;
use Illuminate\Validation\ValidationException;
use OpenApi\Attributes as OA;

class StatusTagController extends Controller
{
    #[OA\Get(
        path: '/status/{statusId}/tags',
        operationId: 'getTagsForStatus',
        tags: ['Status'],
        summary: 'Show all tags for a status which are visible for the current user',
        description: 'Returns a collection of all visible tags for the given status, if user is authorized',
        security: [['passport' => []], ['token' => []]],
        parameters: [
            new OA\Parameter(
                name: 'statusId',
                in: 'path',
                description: 'Status-ID',
                example: 1337,
                schema: new OA\Schema(type: 'integer'),
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'successful operation',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'data',
                            type: 'array',
                            items: new OA\Items(ref: '#/components/schemas/StatusTagResource'),
                        ),
                    ],
                ),
            ),
            new OA\Response(response: 400, description: 'Bad request'),
            new OA\Response(response: 401, description: 'Unauthorized'),
            new OA\Response(response: 404, description: 'No status found for this id'),
            new OA\Response(response: 403, description: 'User not authorized to access this status'),
        ],
    )]
    public function index(int $statusId): JsonResponse
    {
        try {
            $status = Status::findOrFail($statusId);

            return $this->sendResponse(
                data: StatusTagResource::collection(StatusTagBackend::getVisibleTagsForUser($status, auth()->user())),
            );
        } catch (ModelNotFoundException) {
            return $this->sendError(
                error: 'No status found for this id',
            );
        }
    }

    #[OA\Get(
        path: '/statuses/{statusIds}/tags',
        operationId: 'getTagsForMultipleStatuses',
        tags: ['Status'],
        summary: 'Show all tags for multiple statuses which are visible for the current user',
        description: 'Returns a collection of all visible tags for the given statuses, if user is authorized',
        security: [['passport' => []], ['token' => []]],
        parameters: [
            new OA\Parameter(
                name: 'statusIds',
                in: 'path',
                description: 'Status-ID',
                example: '1337,4711',
                schema: new OA\Schema(type: 'string'),
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'successful operation',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'data',
                            type: 'object',
                            properties: [
                                new OA\Property(
                                    property: '1337',
                                    type: 'array',
                                    items: new OA\Items(ref: '#/components/schemas/StatusTagResource'),
                                ),
                                new OA\Property(
                                    property: '4711',
                                    type: 'array',
                                    items: new OA\Items(ref: '#/components/schemas/StatusTagResource'),
                                ),
                            ],
                        ),
                    ],
                ),
            ),
            new OA\Response(response: 400, description: 'Bad request'),
            new OA\Response(response: 401, description: 'Unauthorized'),
            new OA\Response(response: 403, description: 'User not authorized to access this'),
        ],
    )]
    public function indexForMultiple(string $statusIds): JsonResponse
    {
        $statusIds = explode(',', $statusIds);

        foreach ($statusIds as $statusId) {
            if (!is_numeric($statusId)) {
                return $this->sendError(error: 'Id has to be numeric!', code: 400);
            }
        }

        if (count($statusIds) >= 1) {
            $tags = [];
            $statuses = Status::whereIn('id', $statusIds)->get();
            foreach ($statuses as $status) {
                $tags[$status->id] = StatusTagResource::collection(
                    StatusTagBackend::getVisibleTagsForUser($status, auth()->user())
                );
            }

            return $this->sendResponse($tags);
        }

        return $this->sendError(error: 'No statuses found for given ids');
    }

    /**
     * @throws ValidationException
     */
    #[OA\Put(
        path: '/status/{statusId}/tags/{tagKey}',
        operationId: 'updateSingleStatusTag',
        tags: ['Status'],
        summary: 'Update a StatusTag',
        description: 'Updates a single StatusTag Object, if user is authorized to',
        requestBody: new OA\RequestBody(
            content: new OA\MediaType(
                mediaType: 'application/json',
                schema: new OA\Schema(ref: '#/components/schemas/StatusTagResource'),
            ),
        ),
        security: [['passport' => ['write-statuses']], ['token' => []]],
        parameters: [
            new OA\Parameter(
                name: 'statusId',
                in: 'path',
                description: 'Status-ID',
                example: 1337,
                schema: new OA\Schema(type: 'integer'),
            ),
            new OA\Parameter(
                name: 'tagKey',
                in: 'path',
                description: 'Key of StatusTag',
                example: 'seat',
                schema: new OA\Schema(type: 'string'),
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'successful operation',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'data',
                            type: 'object',
                            ref: '#/components/schemas/StatusTagResource',
                        ),
                    ],
                ),
            ),
            new OA\Response(response: 400, description: 'Bad request'),
            new OA\Response(response: 401, description: 'Unauthorized'),
            new OA\Response(response: 404, description: 'No status found for this id'),
            new OA\Response(
                response: 403,
                description: 'User not authorized to manipulate this status',
            ),
        ],
    )]
    public function update(Request $request, int $statusId, string $tagKey): JsonResponse
    {
        // Use the incoming key if provided, otherwise fall back to the existing tag key
        $keyForValidation = $request->input('key', $tagKey);
        $allowedValues = StatusTagKey::tryFrom($keyForValidation)?->allowedValues();

        $validated = $request->validate([
            'key' => ['nullable', 'string', 'max:255'],
            'value' => [
                'required',
                'string',
                'max:255',
                Rule::when($allowedValues !== null, [Rule::in($allowedValues ?? [])]),
            ],
            'visibility' => ['nullable', new Enum(StatusVisibility::class)],
        ]);

        try {
            $status = Status::findOrFail($statusId);
            $statusTag = $status->tags->where('key', $tagKey)->first();
            if ($statusTag === null) {
                throw new ModelNotFoundException();
            }
            $this->authorize('update', $statusTag);
            if (isset($validated['visibility'])) {
                $validated['visibility'] = StatusVisibility::from($validated['visibility']);
            }
            $statusTag->update($validated);
            StatusUpdateEvent::dispatch($status->fresh());

            return $this->sendResponse(data: new StatusTagResource($statusTag));
        } catch (AuthorizationException) {
            return $this->sendError(
                error: 'User not authorized to manipulate this StatusTag',
                code: 403,
            );
        } catch (ModelNotFoundException) {
            return $this->sendError(
                error: 'No StatusTag found for given arguments',
            );
        }
    }

    /**
     * @throws ValidationException
     */
    #[OA\Post(
        path: '/status/{statusId}/tags',
        operationId: 'createSingleStatusTag',
        tags: ['Status'],
        summary: 'Create a StatusTag',
        description: 'Creates a single StatusTag Object, if user is authorized to. <br><br>The key of a tag is free text. You can choose it as you need it. However, <b>please use a namespace for tags</b> (<i>namespace:xxx</i>) that only affect your own application.<br><br>For tags related to standard actions we recommend the following tags in the trwl namespace:<br> <ul> <li>trwl:seat (i.e. 61)</li> <li>trwl:wagon (i.e. 25)</li> <li>trwl:ticket (i.e. BahnCard 100 first))</li> <li>trwl:price (420,69 €)</li> <li>trwl:travel_class (i.e. 1, 2, business, economy, ...)</li> <li>trwl:locomotive_class (BR424, BR450)</li> <li>trwl:journey_number (i.e. 1234. Used as a work-around for missing journey numbers)</li> <li>trwl:wagon_class (i.e. Bpmz)</li> <li>trwl:role (i.e. Tf, Zf, Gf, Lokführer, conducteur de train, ...)</li> <li>trwl:vehicle_number (i.e. 425 001, Tz9001, 123, ...)</li> <li>trwl:passenger_rights (i.e. yes / no / ID of claim)</li> <li>trwl:social_status – social availability indicator. Allowed values: <code>open</code> (open to chatting), <code>open_find_me</code> (open, but staying at seat), <code>open_lets_hang</code> (open and willing to move around), <code>do_not_disturb</code> (prefer not to be disturbed).</li> </ul>',
        requestBody: new OA\RequestBody(
            content: new OA\MediaType(
                mediaType: 'application/json',
                schema: new OA\Schema(ref: '#/components/schemas/StatusTagResource'),
            ),
        ),
        security: [['passport' => ['write-statuses']], ['token' => []]],
        parameters: [
            new OA\Parameter(
                name: 'statusId',
                in: 'path',
                description: 'Status-ID',
                example: 1337,
                schema: new OA\Schema(type: 'integer'),
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'successful operation',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'data',
                            type: 'object',
                            ref: '#/components/schemas/StatusTagResource',
                        ),
                    ],
                ),
            ),
            new OA\Response(response: 400, description: 'Bad request'),
            new OA\Response(response: 401, description: 'Unauthorized'),
            new OA\Response(response: 404, description: 'No status found for this id'),
            new OA\Response(
                response: 403,
                description: 'User not authorized to manipulate this status',
            ),
        ],
    )]
    public function store(Request $request, int $statusId): JsonResponse
    {
        $allowedValues = StatusTagKey::tryFrom($request->input('key'))?->allowedValues();

        $validator = Validator::make($request->all(), [
            'key' => ['required', 'string', 'max:255'],
            'value' => [
                'required',
                'string',
                'max:255',
                Rule::when($allowedValues !== null, [Rule::in($allowedValues ?? [])]),
            ],
            'visibility' => ['required', new Enum(StatusVisibility::class)],
        ]);

        if ($validator->fails()) {
            return $this->sendError(error: $validator->errors(), code: 400);
        }
        $validated = $validator->validate();

        try {
            $status = Status::findOrFail($statusId);

            if ($status->tags->where('key', $validated['key'])->count() > 0) {
                return $this->sendError(
                    error: 'StatusTag with this key already exists',
                    code: 400,
                );
            }
            $this->authorize('update', $status);
            $validated['status_id'] = $status->id;
            $validated['visibility'] = StatusVisibility::from($validated['visibility']);
            $statusTag = StatusTag::create($validated);
            StatusUpdateEvent::dispatch($status->fresh());

            return $this->sendResponse(data: new StatusTagResource($statusTag));
        } catch (AuthorizationException) {
            return $this->sendError(
                error: 'User not authorized to manipulate this Status',
                code: 403,
            );
        } catch (ModelNotFoundException) {
            return $this->sendError(
                error: 'No status found for this id',
            );
        }
    }

    #[OA\Delete(
        path: '/status/{statusId}/tags/{tagKey}',
        operationId: 'destroySingleStatusTag',
        tags: ['Status'],
        summary: 'Destroy a StatusTag',
        description: 'Deletes a single StatusTag Object, if user is authorized to',
        security: [['passport' => ['write-statuses']], ['token' => []]],
        parameters: [
            new OA\Parameter(
                name: 'statusId',
                in: 'path',
                description: 'Status-ID',
                example: 1337,
                schema: new OA\Schema(type: 'integer'),
            ),
            new OA\Parameter(
                name: 'tagKey',
                in: 'path',
                description: 'Key of StatusTag',
                example: 'trwl:seat',
                schema: new OA\Schema(type: 'string'),
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'successful operation',
                content: new OA\JsonContent(
                    properties: [new OA\Property(property: 'status', type: 'string', example: 'success')],
                ),
            ),
            new OA\Response(response: 400, description: 'Bad request'),
            new OA\Response(response: 401, description: 'Unauthorized'),
            new OA\Response(response: 404, description: 'No status found for this id and statusId'),
            new OA\Response(
                response: 403,
                description: 'User not authorized to manipulate this status',
            ),
        ],
    )]
    public function destroy(int $statusId, string $tagKey): JsonResponse
    {
        try {
            $status = Status::findOrFail($statusId);
            $statusTag = $status->tags->where('key', $tagKey)->first();
            if ($statusTag === null) {
                throw new ModelNotFoundException();
            }
            $this->authorize('destroy', $statusTag);
            $statusTag->delete();
            StatusUpdateEvent::dispatch($status->fresh());

            return $this->sendResponse();
        } catch (AuthorizationException) {
            return $this->sendError(
                error: 'User not authorized to manipulate this StatusTag',
                code: 403,
            );
        } catch (ModelNotFoundException) {
            return $this->sendError(
                error: 'No StatusTag found for this arguments',
            );
        }
    }
}
