<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Webhook;
use Illuminate\Auth\Access\HandlesAuthorization;

class WebhookPolicy {
    use HandlesAuthorization;

    /**
     * Determine whether the user can view the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Webhook  $webhook
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function view(User $user, Webhook $webhook) {
        return $webhook->user_id == $user->id;
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Webhook  $webhook
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function delete(User $user, Webhook $webhook) {
        return $user->id == $webhook->user_id;
    }
}
