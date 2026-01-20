<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Webhook;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Auth\Access\Response;

class WebhookPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view the model.
     *
     *
     * @return Response|bool
     */
    public function view(User $user, Webhook $webhook): bool
    {
        return $webhook->user_id === $user->id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Webhook $webhook): bool
    {
        return $user->id === $webhook->user_id;
    }
}
