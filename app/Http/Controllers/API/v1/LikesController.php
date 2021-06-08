<?php

namespace App\Http\Controllers\API\v1;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\Like;
use App\Models\User;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class LikesController extends Controller
{
    /**
     * @param int $status
     * @return AnonymousResourceCollection
     * @todo maybe put this in separate controller?
     */
    public static function show(int $status) {
        return UserResource::collection(User::whereIn('id', Like::where('status_id', $status)->select('id')->get())->get());
    }
}
