<?php

namespace App\Http\Controllers\Backend\Transport;

use App\Http\Controllers\Controller;
use App\Models\Status;
use App\Models\StatusTag;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Gate;

abstract class StatusTagController extends Controller
{
    public static function getVisibleTagsForUser(Status $status, ?User $user = null): Collection {
        return $status->tags->filter(function(StatusTag $tag) use ($user) {
            return Gate::forUser($user)->allows('view', $tag);
        });
    }
}
