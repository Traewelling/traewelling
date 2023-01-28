<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;

abstract class BaseNotification extends Notification
{
    abstract public static function render(mixed $notification): ?string;
}
