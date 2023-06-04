<?php

namespace App\Notifications;

use App\Exceptions\ShouldDeleteNotificationException;
use App\Http\Resources\UserNotificationMessageResource;
use App\Http\Resources\UserResource;
use App\Models\Follow;
use App\Models\FollowRequest;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Notifications\DatabaseNotification;
use JetBrains\PhpStorm\ArrayShape;
use stdClass;

class FollowRequestApproved extends BaseNotification
{
    use Queueable;

    public FollowRequest $followRequest;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(Follow $follow = null) {
        $this->follow = $follow;
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
            'lead'            => __('notifications.userApprovedFollow.lead',
                                    ['followerRequestUsername' => $detail->sender->username]),
            'link'            => route('profile', ['username' => $detail->sender->username]),
            'notice'          => '',
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
            $follow = Follow::findOrFail($data['follow_id']);
            $sender = User::findOrFail($follow->follow_id);
        } catch (ModelNotFoundException) {
            // The follow doesn't exist anymore or the user following you was deleted. Eitherway,
            // we can delete the notification.
            throw new ShouldDeleteNotificationException();
        }
        $notification->detail->follow  = $follow;
        $notification->detail->sender  = new UserResource($sender);
        $notification->detail->message = new UserNotificationMessageResource
        ([
             'icon' => 'fas fa-user-plus',
             'lead' => [
                 'key'    => 'notifications.userApprovedFollow.lead',
                 'values' => [
                     'followerRequestUsername' => $sender->username
                 ]
             ],
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
            'follow_id' => $this->follow->id,
        ];
    }

    public static function getIcon(): string {
        return 'fas fa-user-plus';
    }

    public static function getLead(array $data): string {
        return __('notifications.userApprovedFollow.lead', [
            'followerRequestUsername' => $detail->sender->username, //TODO: username
        ]);
    }

    public static function getNotice(array $data): ?string {
        return null;
    }

    public static function getLink(array $data): ?string {
        return route('profile', [
            'username' => $detail->sender->username, //TODO: username
        ]);
    }
}
