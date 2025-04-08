<?php

namespace App\Notifications;

use Carbon\Carbon;
use Spatie\PersonalDataExport\Notifications\PersonalDataExportedNotification as MainPersonalDataExportedNotification;

class PersonalDataExportedNotification extends MainPersonalDataExportedNotification implements BaseNotification
{

    public function via($notifiable): array {
        return ['mail', 'database'];
    }

    public static function getLead(array $data): string {
        return __('notifications.personalDataExported.lead');
    }

    public static function getNotice(array $data): ?string {
        $date = Carbon::parse($data['deletionDatetime']);
        return __('notifications.personalDataExported.notice', [
            'date' => userTime($date, __('datetime-format')),
        ]);
    }

    public static function getLink(array $data): ?string {
        return route('personal-data-exports', $data['zipFilename']);
    }

    public function toArray(): array
    {
        return [
            'zipFilename' => $this->zipFilename,
            'deletionDatetime' => $this->deletionDatetime,
        ];
    }
}
