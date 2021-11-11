<?php

namespace App\Policies;

use App\Enum\StatusVisibility;
use App\Models\Status;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Auth\Access\Response;

class StatusPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view the model.
     *
     * @param User|null $user
     * @param Status    $status
     *
     * @return Response|bool
     * @todo test unauthenticated
     */
    public function view(?User $user, Status $status): Response|bool {
        // Case 1: User is unauthenticated
        if ($user === null) {
            // true, if user is not private and visibility is UNLISTED or PUBLIC.
            return !$status->user->private_profile && $status->visibility <= StatusVisibility::UNLISTED;
        }

        // Case 2: Status belongs to the user
        if ($user->id === $status->user_id) {
            return Response::allow();
        }

        // Case 3: Status is private and the status doesn't belong to the user
        if ($status->visibility === StatusVisibility::PRIVATE) {
            return Response::deny('Status is private');
        }

        // Case 4: Status is followers only
        if ($status->visibility === StatusVisibility::FOLLOWERS) {
            return $user->follows->contains('id', $status->user_id);
        }

        // Case 5: Status is unlisted
        // This isn't checked here. This is done in the query from the (global/private) dashboard.

        // Case 6: Status is public
        if ($status->visibility === StatusVisibility::PUBLIC) {
            return Response::allow(); //TODO: How to handle with private profile?
        }

        //In any edge case it should be false. Each case should be treated here.
        return Response::deny('Congratulations! You\'ve found an edge-case!');
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param User   $user
     * @param Status $status
     *
     * @return bool
     * @todo test
     */
    public function update(User $user, Status $status): bool {
        return $user->id === $status->user_id;
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param User   $user
     * @param Status $status
     *
     * @return bool
     * @todo test
     */
    public function delete(User $user, Status $status): bool {
        return $user->id === $status->user_id;
    }
}
