<?php

namespace App\Http\Controllers\API\v1;

use App\Exceptions\StatusAlreadyLikedException;
use App\Http\Controllers\API\ResponseController;
use App\Http\Controllers\Controller;
use App\Http\Controllers\StatusController as StatusBackend;
use App\Http\Resources\UserResource;
use App\Models\Like;
use App\Models\Status;
use App\Models\User;
use http\Env\Response;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Auth;

class LikesController extends ResponseController
{
    /**
     * @param int $status
     * @return AnonymousResourceCollection
     * @todo maybe put this in separate controller?
     */
    public function show(int $status): AnonymousResourceCollection {
        return UserResource::collection(
            User::whereIn('id', Like::where('status_id', $status)->select('user_id'))->get()
        );
    }

    /**
     * @param int $status
     * @return JsonResponse
     */
    public function create(int $status): JsonResponse {
        $status = Status::find($status);
        if ($status == null) {
            abort(404);
        }
        try {
            StatusBackend::createLike(Auth::user(), $status);
            return $this->sendv1Response(null, 201);
        } catch (StatusAlreadyLikedException) {
            abort(404);
        }
    }

    /**
     * @param int $status
     * @return JsonResponse
     */
    public function destroy(int $status): JsonResponse {
        $destroyLikeResponse = StatusBackend::DestroyLike(Auth::user(), $status);

        if ($destroyLikeResponse === false) {
            abort(404);
        }
        return $this->sendv1Response();
    }
}
