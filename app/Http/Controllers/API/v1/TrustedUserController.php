<?php declare(strict_types=1);

namespace App\Http\Controllers\API\v1;


use App\Http\Resources\TrustedUserResource;
use App\Models\TrustedUser;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class TrustedUserController extends Controller
{

    /**
     * @OA\Get(
     *     path="/user/{user}/trusted",
     *     operationId="trustedUserIndex",
     *     summary="Get all trusted users for a user",
     *     description="Get all trusted users for the current user or a specific user (admin only).",
     *     tags={"User"},
     *     @OA\Parameter(name="user", in="path", required=true, description="ID of the user (or string 'self' for current user)", @OA\Schema(type="string")),
     *     @OA\Response(response="200", description="List of trusted users"),
     *     @OA\Response(response="401", description="Unauthorized"),
     *     @OA\Response(response="403", description="Forbidden"),
     *     @OA\Response(response="404", description="User not found"),
     *     @OA\Response(response="500", description="Internal Server Error"),
     * )
     * @throws AuthorizationException
     */
    public function index(string|int $userIdOrSelf): AnonymousResourceCollection {
        $user = $this->getUserOrSelf($userIdOrSelf);
        $this->authorize('update', $user);
        return TrustedUserResource::collection($user->trustedUsers()->orderBy('trusted_id')->cursorPaginate(10));
    }

    /**
     * @OA\Post(
     *     path="/user/{user}/trusted",
     *     operationId="trustedUserStore",
     *     summary="Add a user to the trusted users for a user",
     *     description="Add a user to the trusted users for the current user or a specific user (admin only).",
     *     tags={"User"},
     *     @OA\Parameter(name="user", in="path", required=true, description="ID of the user (or string 'self' for current user) who want's to trust.", @OA\Schema(type="string")),
     *     @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(
     *              required={"user_id"},
     *              @OA\Property(property="user_id", type="integer", example="1"),
     *              @OA\Property(property="expires_at", type="string", format="date-time", example="2024-07-28T00:00:00Z")
     *          )
     *     ),
     *     @OA\Response(response="201", description="User added to trusted users"),
     *     @OA\Response(response="400", description="Bad Request"),
     *     @OA\Response(response="401", description="Unauthorized"),
     *     @OA\Response(response="403", description="Forbidden"),
     *     @OA\Response(response="404", description="User not found"),
     *     @OA\Response(response="500", description="Internal Server Error"),
     * )
     * @throws AuthorizationException
     */
    public function store(Request $request, string|int $userIdOrSelf): Response {
        $user        = $this->getUserOrSelf($userIdOrSelf);
        $validated   = $request->validate([
                                              'user_id'    => ['required', 'exists:users,id'],
                                              'expires_at' => ['nullable', 'date', 'after:now'],
                                          ]);
        $trustedUser = User::find($validated['user_id']);
        $this->authorize('update', $user);
        TrustedUser::updateOrCreate(
            [
                'user_id'    => $user->id,
                'trusted_id' => $trustedUser->id,
            ],
            [
                'expires_at' => $validated['expires_at'] ?? null,
            ]
        );
        return response()->noContent(201);
    }

    /**
     * @OA\Delete(
     *     path="/user/{user}/trusted/{trustedId}",
     *     operationId="trustedUserDestroy",
     *     summary="Remove a user from the trusted users for a user",
     *     description="Remove a user from the trusted users for the current user or a specific user (admin only).",
     *     tags={"User"},
     *     @OA\Parameter(name="user", in="path", required=true, description="ID of the user (or string 'self' for current user)", @OA\Schema(type="string")),
     *     @OA\Parameter(name="trusted", in="path", required=true, description="ID of the trusted user", @OA\Schema(type="integer")),
     *     @OA\Response(response="204", description="User removed from trusted users"),
     *     @OA\Response(response="401", description="Unauthorized"),
     *     @OA\Response(response="403", description="Forbidden"),
     *     @OA\Response(response="404", description="User not found"),
     *     @OA\Response(response="500", description="Internal Server Error"),
     * )
     * @throws AuthorizationException
     */
    public function destroy(string|int $userIdOrSelf, int $trusted): Response {
        $user    = $this->getUserOrSelf($userIdOrSelf);
        $trusted = User::findOrFail($trusted);
        $this->authorize('update', $user);
        TrustedUser::where('user_id', $user->id)->where('trusted_id', $trusted->id)->delete();
        return response()->noContent();
    }
}
