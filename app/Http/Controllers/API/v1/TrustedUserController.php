<?php declare(strict_types=1);

namespace App\Http\Controllers\API\v1;


use App\Enum\User\FriendCheckinSetting;
use App\Http\Resources\TrustedUserResource;
use App\Models\TrustedUser;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use stdClass;

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
     *     @OA\Response(response="200", description="List of trusted users",
     *          @OA\JsonContent(
     *              @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/TrustedUserResource")),
     *          )
     *     ),
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
        return TrustedUserResource::collection($user->trustedUsers);
    }

    /**
     * @OA\Get(
     *     path="/user/self/trusted-by",
     *     operationId="trustedByUserIndex",
     *     summary="Get all users who trust the current user",
     *     tags={"User"},
     *     @OA\Response(response="200", description="List of users who trust the current user",
     *          @OA\JsonContent(
     *              @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/TrustedUserResource")),
     *          )
     *     ),
     *     @OA\Response(response="401", description="Unauthorized"),
     *     @OA\Response(response="500", description="Internal Server Error"),
     * )
     * @throws AuthorizationException
     */
    public function indexTrustedBy(): AnonymousResourceCollection {
        $user = auth()->user();

        $friends = $user?->userFollowers
            ->filter(fn(User $otherUser) => $user->userFollowings->contains($otherUser))
            ->filter(fn(User $otherUser) => $otherUser->friend_checkin === FriendCheckinSetting::FRIENDS);

        $trustedByUsers = $user?->trustedByUsers
            ->reject(fn(TrustedUser $trustedUser) => $trustedUser->user->friend_checkin !== FriendCheckinSetting::LIST)
            ->merge($friends)
            ->map(function(TrustedUser|User $user) { //map data to match the TrustedUserResource
                $std             = new stdClass();
                $std->trusted    = $user instanceof TrustedUser ? $user->user : $user;
                $std->expires_at = $user instanceof TrustedUser ? $user->expires_at : null;
                return $std;
            })
            ->unique('trusted.id') //remove duplicates
            ->sortBy('trusted.username', SORT_FLAG_CASE | SORT_NATURAL);

        return TrustedUserResource::collection($trustedByUsers);
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
     *              @OA\Property(property="userId", type="integer", example="1"),
     *              @OA\Property(property="expiresAt", type="string", format="date-time", example="2024-07-28T00:00:00Z", nullable=true),
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
                                              'userId'    => ['required', 'exists:users,id'],
                                              'expiresAt' => ['nullable', 'date', 'after:now'],
                                          ]);
        $trustedUser = User::find($validated['userId']);
        $this->authorize('update', $user);
        TrustedUser::updateOrCreate(
            [
                'user_id'    => $user->id,
                'trusted_id' => $trustedUser->id,
            ],
            [
                'expires_at' => $validated['expiresAt'] ?? null,
            ]
        );
        return response()->noContent(201, ['Content-Type' => 'application/json']);
    }

    /**
     * @OA\Delete(
     *     path="/user/{user}/trusted/{trusted}",
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
        return response()->noContent(204, ['Content-Type' => 'application/json']);
    }
}
