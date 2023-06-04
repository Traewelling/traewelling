<?php

namespace App\Notifications;

use App\Exceptions\ShouldDeleteNotificationException;
use App\Http\Resources\UserNotificationMessageResource;
use App\Http\Resources\UserResource;
use App\Models\FollowRequest;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Notifications\DatabaseNotification;
use JetBrains\PhpStorm\ArrayShape;
use stdClass;

class FollowRequestIssued extends BaseNotification
{
    use Queueable;

    public FollowRequest $followRequest;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(FollowRequest $followRequest = null) {
        $this->followRequest = $followRequest;
    }

    /** @deprecated will be handled in frontend */
    public static function render(mixed $notification): ?string {
        try {
            $detail = self::detail($notification);
        } catch (ShouldDeleteNotificationException) {
            $notification->delete();
            return null;
        }

        return view("includes.notification", [
            'color'           => 'neutral',
            'icon'            => 'fas fa-user-plus',
            'lead'            => __('notifications.userRequestedFollow.lead',
                                    ['followerRequestUsername' => $detail->sender->username]),
            'link'            => route('settings.follower'),
            'notice'          => __('notifications.userRequestedFollow.notice'),
            'date_for_humans' => $notification->created_at->diffForHumans(),
            'read'            => $notification->read_at != null,
            'notificationId'  => $notification->id
        ])->render();
    }

    /**Detail-Handler of notification
     *
     * @param DatabaseNotification $notification
     *
     * @return stdClass
     * @throws ShouldDeleteNotificationException
     */
    public static function detail(DatabaseNotification $notification): stdClass {
        $data                 = $notification->data;
        $notification->detail = new stdClass();
        try {
            $followRequest = FollowRequest::findOrFail($data['follow_id']);
            $sender        = User::findOrFail($followRequest->user_id);
        } catch (ModelNotFoundException $e) {
            // The follow request doesn't exist anymore or the user doesn't exist anymore
            throw new ShouldDeleteNotificationException();
        }
        $notification->detail->followRequest = $followRequest;
        $notification->detail->sender        = new UserResource($sender);
        $notification->detail->message       = new UserNotificationMessageResource
        ([
             'icon'   => 'fas fa-user-plus',
             'lead'   => [
                 'key'    => 'notifications.userRequestedFollow.lead',
                 'values' => [
                     'followerRequestUsername' => $sender->username
                 ]
             ],
             'notice' => [
                 'key'    => 'notifications.userRequestedFollow.notice',
                 'values' => []
             ]
         ]);

        return $notification->detail;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array
     */
    public function via(): array {
        return ['database'];
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array
     */
    #[ArrayShape(['follow_id' => "mixed"])]
    public function toArray(): array {
        return [
            'follow_id' => $this->followRequest->id,
        ];
    }

    public static function getIcon(): string {
        return 'fas fa-user-plus';
    }

    public static function getLead(array $data): string {
        return __('notifications.userRequestedFollow.lead', [
            'followerRequestUsername' => $detail->sender->username, //TODO: username
        ]);
    }

    public static function getNotice(array $data): ?string {
        return __('notifications.userRequestedFollow.notice');
    }

    public static function getLink(array $data): ?string {
        return route('settings.follower');
    }
}
