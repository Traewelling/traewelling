<?php

namespace App\Policies;

use App\Enum\StatusVisibility;
use App\Models\StatusTag;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Auth\Access\Response;

class StatusTagPolicy
{
    use HandlesAuthorization;

    public function view(User $user, StatusTag $statusTag) {
        // Case 1: User is owner of this status
        if ($user->id === $statusTag->status->user_id) {
            return Response::allow('User is owner of this status');
        }

        // Case 2: Status is NOT visible for user
        if (!$user->can('view', $statusTag->status)) {
            return Response::deny('Status is not visible for user');
        }

        // Case 3: StatusTag is private (and the StatusTag doesn't belong to the user)
        if ($statusTag->visibility === StatusVisibility::PRIVATE) {
            return Response::deny('StatusTag is private');
        }

        // Case 4: Status is followers only
        if ($statusTag->visibility === StatusVisibility::FOLLOWERS) {
            return $user->follows->contains('id', $statusTag->status->user_id);
        }

        // Case 5: Status is unlisted
        // This isn't checked here. This is done in the query from the (global/private) dashboard.

        // Case 6: Status is public or authenticated
        if ($statusTag->visibility === StatusVisibility::PUBLIC || $statusTag->visibility === StatusVisibility::AUTHENTICATED) {
            return Response::allow('Status is public or authenticated');
        }

        //In any edge case it should be false. Each case should be treated here.
        return Response::deny('Congratulations! You\'ve found an edge-case!');
    }

    public function update(User $user, StatusTag $statusTag): bool {
        return $statusTag->status->user_id === $user->id;
    }

    public function destroy(User $user, StatusTag $statusTag): bool {
        return $statusTag->status->user_id === $user->id;
    }
}
