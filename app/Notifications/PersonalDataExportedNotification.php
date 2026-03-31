<?php

namespace App\Notifications;

use App\Helpers\Lang;
use Carbon\Carbon;
use Spatie\PersonalDataExport\Notifications\PersonalDataExportedNotification as MainPersonalDataExportedNotification;

class PersonalDataExportedNotification extends MainPersonalDataExportedNotification implements BaseNotification
{
    public function via($notifiable): array
    {
        return ['mail', 'database'];
    }

    public static function getLead(array $data, ?string $locale = null): string
    {
        return Lang::trans('notifications.personalDataExported.lead', [], $locale);
    }

    public static function getNotice(array $data, ?string $locale = null): ?string
    {
        $date = Carbon::parse($data['deletionDatetime']);

        return Lang::trans('notifications.personalDataExported.notice', [
            'date' => userTime($date, __('datetime-format')),
        ], $locale);
    }

    public static function getLink(array $data): ?string
    {
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
