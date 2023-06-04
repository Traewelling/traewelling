<?php

namespace App\Notifications;

use App\Exceptions\ShouldDeleteNotificationException;
use App\Http\Resources\StatusResource;
use App\Http\Resources\UserNotificationMessageResource;
use App\Http\Resources\UserResource;
use App\Models\Like;
use App\Models\Status;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Notifications\DatabaseNotification;
use stdClass;

class StatusLiked extends BaseNotification
{
    use Queueable;

    public ?Like $like;

    /**
     * Create a new notification instance
     *
     * @return void
     */
    public function __construct(Like $like = null) {
        $this->like = $like;
    }

    /** @deprecated will be handled in frontend */
    public static function render($notification): ?string {
        try {
            $detail = self::detail($notification);
        } catch (ShouldDeleteNotificationException) {
            $notification->delete();
            return null;
        }
        $hafas = $detail->status->trainCheckin->hafasTrip;

        return view("includes.notification", [
            'color'           => "neutral",
            'icon'            => "fas fa-heart",
            'lead'            => __('notifications.statusLiked.lead', ['likerUsername' => $detail->sender->username]),
            "link"            => route('statuses.get', ['id' => $detail->status->id]),
            'notice'          => trans_choice(
                'notifications.statusLiked.notice',
                preg_match('/\s/', $hafas->linename),
                [
                    'line'        => $hafas->linename,
                    'createdDate' => Carbon::parse($hafas->departure)->isoFormat(__('date-format'))
                ]
            ),
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
            $like   = Like::findOrFail($data['like_id']);
            $sender = User::findOrFail($like->user_id);
            $status = Status::findOrFail($like->status_id);
        } catch (ModelNotFoundException) {
            // Either the status was unliked, or the sender has deleted its account,
            // or the status was deleted. Eitherway, we don't need the notification anymore.
            throw new ShouldDeleteNotificationException();
        }
        $hafas = $status->trainCheckin->hafasTrip;

        $notification->detail          = new stdClass();
        $notification->detail->sender  = new UserResource($sender);
        $notification->detail->status  = new StatusResource($status);
        $notification->detail->message = new UserNotificationMessageResource
        ([
             'icon'   => 'fas fa-heart',
             'lead'   => [
                 'key'    => 'notifications.statusLiked.lead',
                 'values' => [
                     'likerUsername' => $sender->username
                 ]
             ],
             'notice' => [
                 'key'    => 'notifications.statusLiked.notice',
                 'values' => [
                     'line'        => $hafas->linename,
                     'createdDate' => Carbon::parse($hafas->departure)->isoFormat(__('date-format'))
                 ]
             ]
         ]);

        return $notification->detail;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param mixed
     *
     * @return array
     */
    public function via(): array {
        return ['database'];
    }

    /**
     * Get the array representation of the notification.
     *
     * @param mixed
     *
     * @return array
     */
    public function toArray(): array {
        return [
            'status_id' => $this->like->status_id,
            'like_id'   => $this->like->id,
            'liked_by'  => $this->like->user()->first()->id
        ];
    }

    public static function getIcon(): string {
        return 'fas fa-heart';
    }

    public static function getLead(array $data): string {
        return __('notifications.statusLiked.lead', [
            'likerUsername' => $detail->sender->username, //TODO: username
        ]);
    }

    public static function getNotice(array $data): ?string {
        return trans_choice('notifications.statusLiked.notice',
                            preg_match('/\s/', $hafas->linename), //TODO: linename
                            [
                                'line'        => $hafas->linename,
                                'createdDate' => Carbon::parse($hafas->departure)->isoFormat(__('date-format')) //TODO: departure
                            ]
        );
    }

    public static function getLink(array $data): ?string {
        return route('statuses.get', [
            'id' => $data['status_id'],
        ]);
    }
}
