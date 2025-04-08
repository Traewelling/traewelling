<?php

namespace App\Services\PersonalDataSelection;

use App\Http\Controllers\Backend\User\TokenController;
use App\Models\Event;
use App\Models\EventSuggestion;
use App\Models\Mention;
use App\Models\User;
use App\Models\WebhookCreationRequest;
use Illuminate\Support\Facades\DB;
use Spatie\PersonalDataExport\PersonalDataSelection;

class UserGdprDataService
{
    public function __invoke(PersonalDataSelection $personalDataSelection, User $data): void {
        $this->addUserPersonalData($personalDataSelection, $data);
    }

    private function addUserPersonalData(PersonalDataSelection $personalDataSelection, User $userModel): void {
        $userData = $userModel->only([
                                         'name', 'username', 'home_id', 'private_profile', 'default_status_visibility',
                                         'default_status_sensitivity', 'prevent_index', 'privacy_hide_days', 'language',
                                         'timezone', 'friend_checkin', 'likes_enabled', 'points_enabled', 'mapprovider',
                                         'email', 'email_verified_at', 'privacy_ack_at',
                                         'last_login', 'created_at', 'updated_at'
                                     ]);

        $webhooks = $userModel->webhooks()->with('events')->get();
        $webhooks = $webhooks->map(function($webhook) {
            return $webhook->only([
                                      'oauth_client_id', 'created_at', 'updated_at'
                                  ]);
        });


        if ($userModel->avatar && file_exists(public_path('/uploads/avatars/' . $userModel->avatar))) {
            $personalDataSelection
                ->addFile(public_path('/uploads/avatars/' . $userModel->avatar));
        }

        $personalDataSelection
            ->add('user.json', $userData)
            ->add('notifications.json', $userModel->notifications()->get()->toJson()) //TODO before leaving test-phase: columns definieren per resource
            ->add('likes.json', $userModel->likes()->get()->toJson()) //TODO before leaving test-phase: columns definieren per resource
            ->add('social_profile.json', $userModel->socialProfile()->with('mastodonserver')->get()) //TODO before leaving test-phase: columns definieren per resource
            ->add('event_suggestions.json', EventSuggestion::where('user_id', $userModel->id)->get()->toJson()) //TODO before leaving test-phase: columns definieren per resource
            ->add('events.json', Event::where('approved_by', $userModel->id)->get()->toJson()) //TODO before leaving test-phase: columns definieren per resource
            ->add('webhooks.json', $webhooks)
            ->add(
                'webhook_creation_requests.json',
                WebhookCreationRequest::where('user_id', $userModel->id)->get()->toJson() //TODO before leaving test-phase: columns definieren per resource
            )
            ->add('tokens.json', TokenController::index($userModel)->toJson()) //TODO before leaving test-phase: columns definieren per resource
            ->add('ics_tokens.json', $userModel->icsTokens()->get()->toJson())                                          //TODO before leaving test-phase: columns definieren per resource
            ->add(
                'password_resets.json',
                DB::table('password_resets')->select(['email', 'created_at'])->where('email', $userModel->email)->get() //TODO before leaving test-phase: columns definieren per resource
            )
            ->add('apps.json', $userModel->oAuthClients()->get()->toJson()) //TODO before leaving test-phase: columns definieren per resource
            ->add('follows.json', DB::table('follows')->where('user_id', $userModel->id)->get()) //TODO before leaving test-phase: columns definieren per resource
            ->add('followings.json', DB::table('follows')->where('follow_id', $userModel->id)->get()) //TODO before leaving test-phase: columns definieren per resource
            ->add('blocks.json', DB::table('user_blocks')->where('user_id', $userModel->id)->get()) //TODO before leaving test-phase: columns definieren per resource
            ->add('mutes.json', DB::table('user_mutes')->where('user_id', $userModel->id)->get()) //TODO before leaving test-phase: columns definieren per resource
            ->add('follow_requests.json', DB::table('follow_requests')->where('user_id', $userModel->id)->get()) //TODO before leaving test-phase: columns definieren per resource
            ->add('follows_requests.json', DB::table('follow_requests')->where('follow_id', $userModel->id)->get()) //TODO before leaving test-phase: columns definieren per resource
            ->add('sessions.json', $userModel->sessions()->get()->toJson()) //TODO before leaving test-phase: columns definieren per resource
            ->add('home.json', $userModel->home()->get()->toJson()) //TODO before leaving test-phase: columns definieren per resource
            ->add('hafas_trips.json', DB::table('hafas_trips')->where('user_id', $userModel->id)->get()) //TODO before leaving test-phase: columns definieren per resource
            ->add('mentions.json', Mention::where('mentioned_id', $userModel->id)->get()->toJson()) //TODO before leaving test-phase: columns definieren per resource
            ->add('roles.json', $userModel->roles()->get()->toJson())                                                             //TODO before leaving test-phase: columns definieren per resource
            ->add(
                'activity_log.json',
                DB::table('activity_log')->where('causer_type', get_class($userModel))->where('causer_id', $userModel->id)->get() //TODO before leaving test-phase: columns definieren per resource
            )
            ->add('permissions.json', $userModel->permissions()->get()->toJson()) //TODO before leaving test-phase: columns definieren per resource
            ->add('statuses.json', $userModel->statuses()->with('tags')->get()) //TODO before leaving test-phase: columns definieren per resource
            ->add(
                'reports.json',
                DB::table('reports')
                  ->select('subject_type', 'subject_id', 'reason', 'description', 'reporter_id')
                  ->where('reporter_id', $userModel->id)
                  ->get()                                                       //TODO before leaving test-phase: columns definieren per resource
            )
            ->add('trusted_users.json', DB::table('trusted_users')->where('user_id', $userModel->id)->get()); //TODO before leaving test-phase: columns definieren per resource
    }
}
