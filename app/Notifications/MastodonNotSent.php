<?php

namespace App\Notifications;

use App\Exceptions\ShouldDeleteNotificationException;
use App\Http\Resources\UserNotificationMessageResource;
use App\Models\Status;
use Illuminate\Bus\Queueable;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Notifications\DatabaseNotification;
use stdClass;

class MastodonNotSent extends BaseNotification
{
    use Queueable;

    public $error;
    public $status;

    public function __construct($error, Status $status) {
        $this->error  = $error;
        $this->status = $status;
    }

    /** @deprecated will be handled in frontend */
    public static function render($notification): ?string {
        try {
            $detail = self::detail($notification);
        } catch (ShouldDeleteNotificationException $e) {
            $notification->delete();
            return null;
        }
        $data = $notification->data;


        return view("includes.notification", [
            'color'  => "warning",
            'icon'   => "fas fa-exclamation-triangle",
            'lead'   => __('notifications.socialNotShared.lead', ['platform' => "Mastodon"]),
            "link"   => route('statuses.get', ['id' => $detail->status->id]),
            'notice' => __('notifications.socialNotShared.mastodon.' . $data['error']),

            'date_for_humans' => $notification->created_at->diffForHumans(),
            'read'            => $notification->read_at != null,
            'notificationId'  => $notification->id
        ])->render();
    }

    /**
     * @param DatabaseNotification $notification
     *
     * @return stdClass
     * @throws ShouldDeleteNotificationException
     */
    public static function detail(DatabaseNotification $notification): stdClass {
        $data = $notification->data;

        try {
            $status = Status::findOrFail($data['status_id']);
        } catch (ModelNotFoundException) {
            throw new ShouldDeleteNotificationException();
        }
        $notification->detail          = new stdClass();
        $notification->detail->status  = $status;
        $notification->detail->message = new UserNotificationMessageResource
        ([
             'severity' => 'warning',
             'icon'     => 'fas fa-exclamation-triangle',
             'lead'     => [
                 'key'    => 'notifications.socialNotShared.lead',
                 'values' => [
                     'platform' => 'Mastodon'
                 ]
             ],
             'notice'   => [
                 'key'    => 'notifications.socialNotShared.mastodon.' . $data['error'],
                 'values' => []
             ]
         ]);
        return $notification->detail;
    }

    public function via(): array {
        return ['database'];
    }

    public function toArray(): array {
        return [
            'error'     => $this->error,
            'status_id' => $this->status->id,
        ];
    }

    public static function getIcon(): string {
        return 'fas fa-exclamation-triangle';
    }

    public static function getLead(array $data): string {
        return __('notifications.socialNotShared.lead', [
            'platform' => 'Mastodon',
        ]);
    }

    public static function getNotice(array $data): ?string {
        return __('notifications.socialNotShared.mastodon.' . $data['error']);
    }

    public static function getLink(array $data): ?string {
        return route('statuses.get', ['id' => $data['status_id']]);
    }
}
