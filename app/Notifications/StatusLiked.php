<?php

namespace App\Notifications;

use App\Models\Like;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Date;

class StatusLiked extends Notification implements BaseNotification
{
    use Queueable;

    public Like $like;

    public function __construct(Like $like) {
        $this->like = $like;
    }

    public function via(): array {
        return ['database'];
    }

    public function toArray(): array {
        return [
            'like'   => $this->like->only(['id']),
            'status' => $this->like->status->only(['id']),
            'trip'   => [
                'origin'           => $this->like->status->trainCheckin->originStation->only(['id', 'ibnr', 'name']),
                'destination'      => $this->like->status->trainCheckin->destinationStation->only(['id', 'ibnr', 'name']),
                'plannedDeparture' => $this->like->status->trainCheckin->departure,
                'plannedArrival'   => $this->like->status->trainCheckin->arrival,
                'lineName'         => $this->like->status->trainCheckin->HafasTrip->linename,
            ],
            'liker'  => $this->like->user->only(['id', 'username', 'name']),
        ];
    }

    public static function getLead(array $data): string {
        return __('notifications.statusLiked.lead', [
            'likerUsername' => $data['liker']['username'],
        ]);
    }

    public static function getNotice(array $data): ?string {
        return trans_choice('notifications.statusLiked.notice',
                            preg_match('/\s/', $data['trip']['lineName']),
                            [
                                'line'        => $data['trip']['lineName'],
                                'createdDate' => Date::parse($data['trip']['plannedDeparture'])->isoFormat(__('date-format'))
                            ]
        );
    }

    public static function getLink(array $data): ?string {
        return route('statuses.get', [
            'id' => $data['status']['id'],
        ]);
    }
}
