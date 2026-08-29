<?php

declare(strict_types=1);

namespace App\Http\Controllers\API\v1;

use App\Exceptions\RateLimitExceededException;
use App\Http\Resources\AdminUserListResource;
use App\Http\Resources\AdminUserResource;
use App\Models\User;
use App\Repositories\PrivacyPolicyRepository;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\Rule;
use OpenApi\Attributes as OA;

class AdminUserController extends Controller
{
    public function __construct(
        private readonly PrivacyPolicyRepository $privacyPolicyRepository,
    ) {}

    #[OA\Get(
        path: '/v1/admin/users',
        operationId: 'getAdminUsers',
        description: 'Admin only. Returns a cursor-paginated list of all users, optionally filtered by a search query.',
        summary: 'List users',
        security: [['passport' => []], ['token' => []]],
        tags: ['Admin'],
        parameters: [
            new OA\Parameter(name: 'cursor', in: 'query', required: false, schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'query', in: 'query', required: false, schema: new OA\Schema(type: 'string')),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Paginated list of users',
                content: new OA\JsonContent(
                    required: ['data'],
                    properties: [
                        new OA\Property(
                            property: 'data',
                            type: 'array',
                            items: new OA\Items(ref: AdminUserListResource::class),
                        ),
                    ],
                ),
            ),
            new OA\Response(response: 403, description: 'Forbidden'),
        ],
    )]
    public function index(Request $request): AnonymousResourceCollection
    {
        $this->authorize('adminViewAny', User::class);

        $search = $request->query('query');
        $users = User::query();

        if ($search) {
            if (preg_match('/^["\'"“„].*["\'"”„]$/u', $search)) {
                $exact = mb_substr($search, 1, -1);
                $users->where(static function ($q) use ($exact) {
                    $q->where('id', $exact)
                        ->orWhere('name', $exact)
                        ->orWhere('username', $exact)
                        ->orWhere('email', $exact);
                });
            } else {
                $users->where(static function ($q) use ($search) {
                    $q->where('id', $search)
                        ->orWhere('name', 'like', '%' . $search . '%')
                        ->orWhere('username', 'like', '%' . $search . '%')
                        ->orWhere('email', 'like', '%' . $search . '%');
                });
            }
        }

        return AdminUserListResource::collection(
            $users->orderByDesc('last_login')->cursorPaginate(25),
        );
    }

    #[OA\Get(
        path: '/v1/admin/users/{id}',
        operationId: 'getAdminUser',
        description: 'Admin only. Returns full details for a single user including stats, roles, mail changes, and recent statuses.',
        summary: 'Get user details',
        security: [['passport' => []], ['token' => []]],
        tags: ['Admin'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'User details',
                content: new OA\JsonContent(
                    required: ['data'],
                    properties: [new OA\Property(property: 'data', ref: AdminUserResource::class)],
                ),
            ),
            new OA\Response(response: 403, description: 'Forbidden'),
            new OA\Response(response: 404, description: 'Not found'),
        ],
    )]
    public function show(int $id): AdminUserResource
    {
        $this->authorize('adminViewAny', User::class);

        $user = User::with(['mailChanges' => fn ($q) => $q->orderByDesc('created_at')])->findOrFail($id);

        $user->setRelation('statuses', $user->statuses()
            ->with([
                'user',
                'checkin.trip.stopovers.station',
                'checkin.originStopover.station',
                'checkin.destinationStopover.station',
            ])
            ->orderByDesc('created_at')
            ->limit(15)
            ->get());

        $current = $this->privacyPolicyRepository->getPrivacyPolicyValidAt(now());
        $future = $this->privacyPolicyRepository->getUpcomingPrivacyPolicy();
        $acceptedCurrent = $this->privacyPolicyRepository->getUserPolicyAcceptance($user, $current)->first();
        $acceptedFuture = $future
            ? $this->privacyPolicyRepository->getUserPolicyAcceptance($user, $future)->first()
            : null;

        $user->setAttribute('privacyPolicyCurrent', $acceptedCurrent?->accepted_at?->toIso8601String());
        $user->setAttribute('privacyPolicyFuture', $acceptedFuture?->accepted_at?->toIso8601String());
        $user->setAttribute('privacyPolicyFutureExists', $future !== null);

        return new AdminUserResource($user);
    }

    #[OA\Put(
        path: '/v1/admin/users/{id}/email',
        operationId: 'updateAdminUserEmail',
        description: 'Admin only. Updates the email address for a user and sends a verification notification.',
        summary: 'Update user email',
        security: [['passport' => []], ['token' => []]],
        tags: ['Admin'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['email'],
                properties: [new OA\Property(property: 'email', type: 'string', format: 'email')],
            ),
        ),
        responses: [
            new OA\Response(response: 204, description: 'Email updated'),
            new OA\Response(response: 403, description: 'Forbidden'),
            new OA\Response(response: 404, description: 'Not found'),
            new OA\Response(response: 422, description: 'Validation error'),
        ],
    )]
    public function updateEmail(Request $request, int $id): Response
    {
        $this->authorize('adminViewAny', User::class);

        $user = User::findOrFail($id);
        $validated = $request->validate([
            'email' => ['required', 'email', Rule::unique('users', 'email')->ignore($id)],
        ]);

        $user->email = $validated['email'];
        $user->save();

        try {
            $user->sendEmailVerificationNotification();
        } catch (RateLimitExceededException) {
            // Ignore
        }

        if ($user->password === null) {
            Password::sendResetLink(['email' => $validated['email']]);
        }

        return response()->noContent();
    }

    #[OA\Put(
        path: '/v1/admin/users/{id}/roles',
        operationId: 'updateAdminUserRoles',
        description: 'Admin only. Syncs roles for a user. The admin role is protected and cannot be removed.',
        summary: 'Update user roles',
        security: [['passport' => []], ['token' => []]],
        tags: ['Admin'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['roles'],
                properties: [
                    new OA\Property(property: 'roles', type: 'array', items: new OA\Items(type: 'string')),
                ],
            ),
        ),
        responses: [
            new OA\Response(response: 204, description: 'Roles updated'),
            new OA\Response(response: 403, description: 'Forbidden'),
            new OA\Response(response: 404, description: 'Not found'),
            new OA\Response(response: 422, description: 'Validation error'),
        ],
    )]
    public function updateRoles(Request $request, int $id): Response
    {
        $this->authorize('adminViewAny', User::class);

        $user = User::findOrFail($id);
        $validated = $request->validate([
            'roles' => ['array'],
            'roles.*' => ['string', 'exists:roles,name'],
        ]);

        $roles = collect($validated['roles'] ?? [])
            ->reject(fn ($name) => $name === 'admin')
            ->toArray();

        if ($user->hasRole('admin')) {
            $roles[] = 'admin';
        }

        $user->syncRoles($roles);

        return response()->noContent();
    }
}
