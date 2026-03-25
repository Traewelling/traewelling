<?php

declare(strict_types=1);

namespace App\Observers;

use App\Jobs\AdminNotification\DeleteAdminNotification;
use App\Jobs\AdminNotification\SendAdminNotification;
use App\Models\EventSuggestion;

class EventSuggestionObserver
{
    public function created(EventSuggestion $suggestion): void
    {
        SendAdminNotification::dispatch(
            type: 'events',
            htmlMessage: strtr('<b>New event suggestion:</b>' . PHP_EOL .
                               'Title: :name' . PHP_EOL .
                               'Begin: :begin' . PHP_EOL .
                               'End: :end' . PHP_EOL .
                               'Suggested by user: :username' . PHP_EOL .
                               '<a href=":url">Review suggestion</a>', [
                                   ':name' => $suggestion->name,
                                   ':begin' => $suggestion->begin->format('d.m.Y'),
                                   ':end' => $suggestion->end->format('d.m.Y'),
                                   ':username' => $suggestion->user->username,
                                   ':url' => config('app.url') . '/admin/event-suggestions/' . $suggestion->id,
                               ]),
            model: $suggestion,
        );
    }

    public function updated(EventSuggestion $suggestion): void
    {
        if (!$suggestion->getOriginal('processed') && $suggestion->processed) {
            DeleteAdminNotification::dispatch(
                type: 'events',
                telegramId: $suggestion->telegram_notification_id,
                matrixEventId: $suggestion->matrix_notification_id,
            );
        }
    }
}
